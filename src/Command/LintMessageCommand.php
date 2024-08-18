<?php

declare(strict_types=1);

namespace GarettRobson\PhpCommitLint\Command;

use Exception;
use GarettRobson\PhpCommitLint\Linter\ConventionalCommitsMessageParser;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class LintMessageCommand extends Command
{
    protected Filesystem $filesystem;

    public function __construct()
    {
        parent::__construct('message:lint');
        $this->filesystem = new Filesystem();
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

    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        $io = new SymfonyStyle($input, $output);

        $message =
            $this->readFile($input->getArgument('file')) ??
            stream_get_contents(STDIN)
        ;

        if(!$message) {
            throw new Exception(sprintf(
                'No message to parse'
            ));
        }


        $messageParser = new ConventionalCommitsMessageParser($message);

        $io->writeln('<info>Message read:</info>', $io::VERBOSITY_VERBOSE);
        $io->writeln(sprintf('<comment>%s</comment>', $message), $io::VERBOSITY_VERBOSE);

        $io->success('Message lint successful');

        return static::SUCCESS;
    }

    protected function readFile($path)
    {
        if($path === null) {
            return null;
        }
        return $this->filesystem->readFile($path);
    }

}
