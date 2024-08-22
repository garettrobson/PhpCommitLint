<?php

declare(strict_types=1);

namespace GarettRobson\PhpCommitLint\Command;

use GarettRobson\PhpCommitLint\Validation\ValidatorConfiguration;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;

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
            ->addOption(
                'no-override',
                'O',
                InputOption::VALUE_NONE,
                'Do not load any local overrides'
            )
            ->addOption(
                'no-home',
                'H',
                InputOption::VALUE_NONE,
                'Do not load any local overrides'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $includePaths = $input->getOption('include') ?? [];
        $includes = [
            __DIR__.'/../../res/rules.json',
            ...(array) $includePaths,
        ];

        if (!$input->getOption('no-home')) {
            $this->includeHomeOverridePath($io, $includes);
        }

        if (!$input->getOption('no-override')) {
            $this->includeLocalOverridePath($io, $includes);
        }

        $this->prepareValidatorConfiguration($io, $includes);

        return self::SUCCESS;
    }

    /**
     * Undocumented function.
     *
     * @param array<string> $includes
     */
    protected function includeHomeOverridePath(SymfonyStyle $io, array &$includes): void
    {
        $home = Path::getHomeDirectory();

        if (!$home) {
            return;
        }

        $targetFile = Path::makeAbsolute('.php-commit-lint.json', $home);

        if ($this->filesystem->exists($targetFile)) {
            $includes[] = $targetFile;

            $io->writeln(
                sprintf(
                    '<info>Using home override:</info> <comment>%s</comment>',
                    $targetFile
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

    /**
     * Undocumented function.
     *
     * @param array<string> $includes
     */
    protected function includeLocalOverridePath(SymfonyStyle $io, array &$includes): void
    {
        if ($overridePath = $this->findLocalFile('.php-commit-lint.json')) {
            $includes[] = $overridePath;

            $io->writeln(
                sprintf(
                    '<info>Using local override:</info> <comment>%s</comment>',
                    Path::makeRelative($overridePath, (string) getcwd())
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

    protected function findLocalFile(string $file, bool $returnIncludeFilename = true): false|string
    {
        $targetDirectory = getcwd();

        if (false === $targetDirectory) {
            return false;
        }

        // From the CWD towards the root of the system, locate any
        // .php-commit-lint.json files, returns the first one found
        $dirs = [];
        do {
            $dirs[] = $targetDirectory;
            $targetFile = $targetDirectory.DIRECTORY_SEPARATOR.$file;
            if ($this->filesystem->exists($targetFile)) {
                return $returnIncludeFilename ? $targetFile : $targetDirectory;
            }
            $targetDirectory = dirname($targetDirectory);
        } while (!in_array($targetDirectory, $dirs, true));

        // Return false if not found
        return false;
    }

    /**
     * @param array<string> $includes
     */
    protected function prepareValidatorConfiguration(SymfonyStyle $io, array &$includes): void
    {
        $this->validationConfiguration = new ValidatorConfiguration();

        if ($io->getVerbosity() >= $io::VERBOSITY_VERY_VERBOSE) {
            $io->section('Including files');
        }
        foreach ($includes as $include) {
            if (!is_string($include)) {
                throw new \RuntimeException(sprintf(
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
