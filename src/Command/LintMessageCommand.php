<?php

namespace GarettRobson\PhpCommitLint\Command;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class LintMessageCommand extends Command
{

	public function __construct()
	{
		parent::__construct('message:lint');
	}

	protected function configure(): void
	{
		$this
			->setDescription('Lint a commit message')
			->setHelp('Analyse a commit message and report errors')
			->addArgument(
				'file',
				InputArgument::OPTIONAL,
				'File to lint the contents of (Alternatively use < redirect)'
			)
			->addOption(
				'rule',
				'r',
				InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
				'Linting rules to use',
				[]
			)
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$message = null;

		$message =
			$this->readFile($input->getArgument('file')) ??
			stream_get_contents(STDIN)
		;

		$output->write($message);

		return static::SUCCESS;
	}

	protected function readFile($path) {
		if($path === null) {
			return null;
		}
		$filesystem = new Filesystem();
		return $filesystem->readFile($path);
	}

}
