<?php

declare(strict_types=1);

namespace GarettRobson\PhpCommitLint\Command;

use Composer\InstalledVersions;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Process\Process;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
				null,
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

        $this->runProcess(['git', '-C', $installPath, 'fetch']);

        if($branch = $input->getOption('dev')) {
            $this->runProcess(['git', '-C', $installPath, 'checkout', $branch]);
            $this->runProcess(['git', '-C', $installPath, 'pull']);
        } else {
            $tags = $this->runProcess(['git', '-C', $installPath, 'tag', '--sort', '-v:refname']);
            preg_match('/^[^\n]+/', $tags, $matches, PREG_UNMATCHED_AS_NULL);
            $tag = $matches[0];
            $this->runProcess(['git', '-C', $installPath, 'checkout', $tag]);
        }

        return self::SUCCESS;
    }

    protected function runProcess(array $command): string {
        $process = new Process($command);

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
