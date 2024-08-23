<?php

declare(strict_types=1);

namespace GarettRobson\PhpCommitLint\Command;

use GarettRobson\PhpCommitLint\Message\ConventionalCommitsMessageParser;
use GarettRobson\PhpCommitLint\Validation\Rule;
use GarettRobson\PhpCommitLint\Validation\Validator;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class LintCommand extends PhpCommitLintCommand
{
    public function __construct()
    {
        parent::__construct('lint');
    }

    /**
     * @return array<Rule>
     */
    public function getRules(): array
    {
        $rules = [];
        foreach ($this->validationConfiguration->getRules() as $rule) {
            $class = $rule->class;
            $parameters = $rule->parameters ?? [];

            if (!is_string($class)) {
                throw new \RuntimeException(sprintf(
                    'Expected class type of string, received %s',
                    gettype($class),
                ));
            }
            if (!class_exists($class, true)) {
                throw new \RuntimeException(sprintf(
                    'Class %s does not exist',
                    $class,
                ));
            }
            if (!is_subclass_of($class, Rule::class, true)) {
                throw new \RuntimeException(sprintf(
                    'Expected %s to be subclass of %s, parents are %s',
                    $class,
                    Rule::class,
                    implode(', ', class_parents($class)),
                ));
            }

            $rule = new $class($rule);
            $rules[] = $rule;
        }

        return $rules;
    }

    protected function configure(): void
    {
        parent::configure();
        $this
            ->setDescription('Lint a commit message')
            ->setHelp(<<<'HELP'
When installed as a composer dependency you can simply symlink the executable to <comment>commit-msg</comment> using:
<info>ln -s ../../vendor/bin/php-commit-lint-commit-msg .git/hooks/commit-msg</info>
HELP)
            ->addArgument(
                'file',
                InputArgument::OPTIONAL,
                'File to lint the contents of, displays help if omitted'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('PHP Commit Lint: Message Lint');

        parent::execute($input, $output);

        $commitMessage = false;

        $file = $input->getArgument('file');
        if (null == $file) {
            if (posix_isatty(STDIN)) {
                if ($application = $this->getApplication()) {
                    return $application->doRun(
                        new ArrayInput([
                            'lint',
                            '--help' => true,
                        ]),
                        $output
                    );
                }

                throw new \RuntimeException('Failed to retrieve application context');
            }

            $commitMessage = stream_get_contents(STDIN, -1);
        } elseif (!is_string($file)) {
            throw new \RuntimeException(sprintf(
                'Expected file argument to be a string, received %s',
                gettype($file),
            ));
        } else {
            $commitMessage = $this->filesystem->readFile($file);
            if (!$commitMessage) {
                throw new \Exception(sprintf(
                    'No message to parse in %s',
                    $file
                ));
            }
        }

        if (!$commitMessage) {
            throw new \Exception(sprintf(
                'No message to parse',
            ));
        }

        if ($io->getVerbosity() >= $io::VERBOSITY_VERY_VERBOSE) {
            $io->section('Commit message');
            $io->writeln(sprintf('<text>%s</text>', $commitMessage));
        }

        $messageParser = new ConventionalCommitsMessageParser();
        $message = $messageParser->parseMessage($commitMessage);

        if ($io->getVerbosity() >= $io::VERBOSITY_VERY_VERBOSE) {
            $io->section('Message');
            $io->writeln(json_encode($message, JSON_PRETTY_PRINT) ?: '');
        }

        $validator = new Validator($this->getRules());

        $errors = $validator->validate($message);

        if ($errors) {
            $io->section('Errors');
            foreach ($errors as $error) {
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
}
