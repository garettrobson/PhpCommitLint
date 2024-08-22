<?php

declare(strict_types=1);

namespace GarettRobson\PhpCommitLint\Command;

use RuntimeException;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use GarettRobson\PhpCommitLint\Validation\ValidatorConfiguration;

abstract class PhpCommitLintCommand extends Command
{
    protected Filesystem $filesystem;
    protected ValidatorConfiguration $validationConfiguration;

    public function __construct(string $name)
    {
        parent::__construct($name);
        $this->filesystem = new Filesystem();
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                'include',
                'i',
                InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED,
                'Include additional configuration files',
                []
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $includePaths = $input->getOption('include') ?? [];
        $includes = [
            __DIR__ . '/../../res/rules.json',
            ...(array)$includePaths
        ];

        $this->includeLocalOverridePath($io, $includes);
        $this->prepareValidatorConfiguration($io, $includes);

        return self::SUCCESS;

    }

    /**
     * Undocumented function
     *
     * @param SymfonyStyle $io
     * @param array<string> $includes
     * @return void
     */
    protected function includeLocalOverridePath(SymfonyStyle $io, array &$includes)
    {
        if ($overridePath = $this->findLocalFile('.php-commit-lint.json')) {
            $includes[] = $overridePath;

            $io->writeln(
                sprintf(
                    '<info>Using local override:</info> <comment>%s</comment>',
                    Path::makeRelative($overridePath, (string)getcwd())
                ),
                $io::VERBOSITY_VERBOSE
            );
        } else {
            $io->writeln(
                '<comment>No local override found</comment>',
                $io::VERBOSITY_VERBOSE
            );
        }
        $io->writeln('', $io::VERBOSITY_VERBOSE);
    }

    protected function findLocalFile(string $file): string|false
    {

        $target = getcwd();

        if ($target === false) {
            return false;
        }

        // From the CWD towards the root of the system, locate any
        // .php-commit-lint.json files, returns the first one found
        $dirs = [];
        do {
            $dirs[] = $target;
            $path = $target . DIRECTORY_SEPARATOR . $file;
            if ($this->filesystem->exists($path)) {
                return $path;
            }
            $target = dirname($target);
        } while (!in_array($target, $dirs, true));

        // Return false if not found
        return false;
    }

    /**
     * @param SymfonyStyle $io
     * @param array<string> $includes
     * @return void
     */
    protected function prepareValidatorConfiguration(SymfonyStyle $io, array &$includes): void
    {
        $this->validationConfiguration = new ValidatorConfiguration();

        if ($io->getVerbosity() >= $io::VERBOSITY_VERY_VERBOSE) {
            $io->section('Including files');
        }
        foreach ($includes as $include) {
            if (!is_string($include)) {
                throw new RuntimeException(sprintf(
                    'Expected list of includes to contain strings, received %s',
                    gettype($include)
                ));
            }
            $include = Path::canonicalize($include);
            $include = $this->validationConfiguration->includeFile($include);
            if ($io->getVerbosity() >= $io::VERBOSITY_VERY_VERBOSE) {
                foreach ($include as $path) {
                    $io->writeln(sprintf('<text>%s</text>', $path));
                }
            }
        }
        if ($io->getVerbosity() >= $io::VERBOSITY_VERY_VERBOSE) {
            $io->writeln('');
        }
    }

}
