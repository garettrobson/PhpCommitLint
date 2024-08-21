<?php

declare(strict_types=1);

namespace GarettRobson\PhpCommitLint\Command;

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

        $includes = [
            __DIR__ . '/../../res/rules.json',
            ...($input->getOption('include') ?? [])
        ];

        if ($overridePath = $this->getLocalOverridePath()) {
            $includes[] = $overridePath;

            $io->writeln(
                sprintf(
                    '<info>Using local override:</info> <comment>%s</comment>',
                    Path::makeRelative($overridePath, (string)getcwd())
                ),
                $io::VERBOSITY_VERBOSE
            );
        } else {
            $io->writeln('<comment>No local override found</comment>', $io::VERBOSITY_VERBOSE);
        }
        $io->writeln('', $io::VERBOSITY_VERBOSE);

        $this->validationConfiguration = new ValidatorConfiguration();
        if ($io->getVerbosity() >= $io::VERBOSITY_VERY_VERBOSE) {
            $io->section('Including files');
        }
        foreach ($includes as $include) {
            $include = Path::canonicalize($include);
            $include = $this->validationConfiguration->includeFile($include);
            if ($io->getVerbosity() >= $io::VERBOSITY_VERY_VERBOSE) {
                foreach($include as $path) {
                    $io->writeln($path);
                }
            }
        }
        if ($io->getVerbosity() >= $io::VERBOSITY_VERY_VERBOSE) {
            $io->writeln('');
        }

        return self::SUCCESS;

    }

    protected function getLocalOverridePath(): string|false
    {

        $target = getcwd();

        if ($target === false) {
            return false;
        }

        $dirs = [];

        // Get all the directories to check, from the CWD towards the root of the system
        do {
            $dirs[] = $target;
            $target = dirname($target);
        } while (!in_array($target, $dirs, true));

        // Return the first .php-commit-lint.json files found
        foreach ($dirs as $dir) {
            $target = $dir . '/.php-commit-lint.json';
            if ($this->filesystem->exists($target)) {
                return $target;
            }
        }

        // Return false if not found
        return false;
    }

}
