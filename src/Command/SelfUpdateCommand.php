<?php

declare(strict_types=1);

namespace GarettRobson\PhpCommitLint\Command;

use Composer\InstalledVersions;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Process\Process;

class SelfUpdateCommand extends PhpCommitLintCommand
{
    public function __construct()
    {
        parent::__construct('self-update');
    }

    protected function configure(): void
    {
        parent::configure();
        $this
            ->setDescription('Updates php-commit-lint')
            ->addOption(
                'dev',
                null,
                InputOption::VALUE_OPTIONAL,
                'Go onto the main branch',
                false
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('PHP Commit Lint: Message Lint');

        parent::execute($input, $output);

        $helper = $this->getHelper('process');

        $rootPackage = InstalledVersions::getRootPackage();
        $installPath = $rootPackage['install_path'];
        $installPath = Path::canonicalize($installPath);

        $this->runProcess($io, ['git', '-C', $installPath, 'fetch']);

        if (false === $input->getOption('dev')) {
            $tags = $this->runProcess($io, ['git', '-C', $installPath, 'tag', '--sort', '-v:refname']);
            preg_match('/^[^\n]+/', $tags, $matches, PREG_UNMATCHED_AS_NULL);
            $tag = $matches[0] ?? false;
            if (!$tag) {
                throw new \RuntimeException(sprintf(
                    "Could not determine any tags:\n%s",
                    $tags,
                ));
            }
            $this->runProcess($io, ['git', '-C', $installPath, 'checkout', $tag]);
        } else {
            $branch = $input->getOption('dev') ?? 'main';
            if (!is_string($branch)) {
                throw new \RuntimeException(sprintf(
                    'Branch did not resolve to a string: %s',
                    json_encode($branch),
                ));
            }
            $this->runProcess($io, ['git', '-C', $installPath, 'checkout', $branch]);
            $this->runProcess($io, ['git', '-C', $installPath, 'pull']);
        }

        return self::SUCCESS;
    }

    /**
     * @param array<string> $command
     */
    protected function runProcess(SymfonyStyle $io, array $command): string
    {
        $process = new Process($command);

        if ($io->getVerbosity() >= $io::VERBOSITY_VERBOSE) {
            $io->writeln(sprintf(
                'Running command <info>%s</info>',
                implode(' ', $command),
            ));
        }

        $process->run();

        if (!$process->isSuccessful()) {
            throw new \RuntimeException(sprintf(
                "Error performing command %s:\n%s",
                implode(' ', $command),
                $process->getErrorOutput(),
            ));
        }

        return $process->getOutput();
    }
}
