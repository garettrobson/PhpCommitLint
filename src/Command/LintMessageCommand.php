<?php

declare(strict_types=1);

namespace GarettRobson\PhpCommitLint\Command;

use Exception;
use RuntimeException;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use GarettRobson\PhpCommitLint\Validation\Validator;
use Symfony\Component\Console\Output\OutputInterface;
use GarettRobson\PhpCommitLint\Validation\ValidatorConfiguration;
use GarettRobson\PhpCommitLint\Message\ConventionalCommitsMessageParser;

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
            ->setHelp(<<<HELP
When installed as a composer dependency symlink the executable to <comment>commit-msg</comment> using:
<info>ln -s ../../vendor/bin/php-commit-lint .git/hooks/commit-msg</info>
HELP)
            ->addArgument(
                'file',
                InputArgument::REQUIRED,
                'File to lint the contents of'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('PHP Commit Lint: Message Lint');

        $messageText = $this->readFile($input->getArgument('file'));
        if(!$messageText) {
            throw new Exception(sprintf(
                'No message to parse'
            ));
        }
        $io->writeln('<info>Input Message:</info>', $io::VERBOSITY_VERY_VERBOSE);
        $io->writeln(sprintf('<comment>%s</comment>', $messageText), $io::VERBOSITY_VERY_VERBOSE);

        $messageParser = new ConventionalCommitsMessageParser();
        $message = $messageParser->parseMessage($messageText);

        $includes = [
            __DIR__ . '/../../res/rules.json',
        ];

        if($overridePath = $this->getLocalOverridePath()) {
            $io->writeln(
                sprintf(
                    '<info>Using override <comment>%s</comment></info>',
                    Path::makeRelative($overridePath, getcwd())
                ),
                $io::VERBOSITY_VERBOSE
            );
            $includes[] = $overridePath;
        } else {
            $io->writeln(
                'No override found',
                $io::VERBOSITY_VERBOSE
            );
        }

        $validationConfiguration = new ValidatorConfiguration();
        foreach($includes as $include) {
            $validationConfiguration->includeFile($include);
        }

        $validator = new Validator($validationConfiguration->getRules());

        $errors = $validator->validate($message);

        if($errors) {
            $output->writeln("<error>The following errors occurred:</error>");
            foreach($errors as $error) {
                $output->writeln(sprintf(
                    '- %s',
                    $error
                ));
            }
            $output->writeln('');
            $io->error('Commit message failed linting');
            return static::FAILURE;
        }

        $io->success('Commit message passed linting');
        return static::SUCCESS;
    }

    protected function readFile($path)
    {
        if ($path === null) {
            return null;
        }

        return $this->filesystem->readFile($path);
    }

    protected function getLocalOverridePath()
    {

        $target = getcwd();
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
