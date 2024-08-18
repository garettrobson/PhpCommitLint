<?php

declare(strict_types=1);

namespace GarettRobson\PhpCommitLint\Command;

use Exception;
use RuntimeException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Console\Command\Command;
use GarettRobson\PhpCommitLint\Linter\Validator;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use GarettRobson\PhpCommitLint\Rules\LineLengthRule;
use Symfony\Component\Console\Output\OutputInterface;
use GarettRobson\PhpCommitLint\Rules\ConventionalCommitsRule;
use GarettRobson\PhpCommitLint\Linter\ConventionalCommitsMessageParser;

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
                'File to lint the contents of'
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

        $messageText = $this->readFile($input->getArgument('file'));

        if(!$messageText) {
            throw new Exception(sprintf(
                'No message to parse'
            ));
        }

        $io->writeln('<info>Message read:</info>', $io::VERBOSITY_VERY_VERBOSE);
        $io->writeln(sprintf('<comment>%s</comment>', $messageText), $io::VERBOSITY_VERY_VERBOSE);

        $messageParser = new ConventionalCommitsMessageParser();
        $message = $messageParser->parseMessage($messageText);


        $validator = new Validator();
        $validator
            ->addRule(new LineLengthRule())
            ->addRule(new ConventionalCommitsRule())
        ;
        $errors = $validator->validate($message);

        if($errors) {
            foreach($errors as $error) {
                $io->error($error);
            }
            return static::FAILURE;
        }

        $io->success('Message lint successful');
        return static::SUCCESS;
    }

    protected function readFile($path)
    {
        if($path === null) {
            return null;
        }

        if(!$this->filesystem->exists($path)) {
            throw new RuntimeException(sprintf(
                'File not found: %s',
                $path
            ));
        } elseif(!is_readable($path)) {
            throw new RuntimeException(sprintf(
                'Unable to read: %s',
                $path
            ));
        } elseif(is_dir($path)) {
            throw new RuntimeException(sprintf(
                'Expected file, received directory: %s',
                $path
            ));
        }

        return $this->filesystem->readFile($path);
    }

}
