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
use GarettRobson\PhpCommitLint\Rules\PropertyRegexRule;
use GarettRobson\PhpCommitLint\Rules\PropertyRequiredRule;
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

        $validator = $this->getValidator($io);
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

    protected function getValidator(SymfonyStyle $io): Validator
    {
        $validator = new Validator();
        $configuration = $this->getConfiguration();
        $rules = [];

        foreach ($configuration['rules'] as $rule) {
            if (substr($rule, 0, 1) === '@') {
                $ruleName = substr($rule, 1);
                if (!isset($configuration[$ruleName])) {
                    throw new RuntimeException(sprintf(
                        'Rule %s not found',
                        $ruleName,
                    ));
                }

                $rules = array_merge(
                    $rules,
                    $configuration[$ruleName],
                );
            } else {
                $rules[] = $rule;
            }
        }

        $io->writeln('<info>Rules:</info>', $io::VERBOSITY_VERBOSE);
        foreach ($rules as $key => $rule) {
            $class = $rule['class'];
            $parameters = $rule['parameters'] ?? [];
            $validator->addRule(new $class(...$parameters));
            $io->writeln(
                sprintf(
                    '- <comment>[%s]</comment> %s(%s)',
                    $key,
                    $rule['class'],
                    implode(', ', array_map(
                        fn($value) => sprintf(
                            '<info>%s</info>',
                            json_encode($value)
                        ),
                        $parameters
                    ))
                ),
                $io::VERBOSITY_VERBOSE
            );
        }
        $io->writeln('', $io::VERBOSITY_VERBOSE);

        return $validator;
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

    public function getConfiguration(): array
    {
        $configuration = array_merge(
            [
                'rules' => [
                    '@basic',
                    '@conventional-commits',
                ]
            ],
            json_decode($this->filesystem->readfile(__DIR__ . '/../../res/rules/basic.json'), true),
            json_decode($this->filesystem->readfile(__DIR__ . '/../../res/rules/conventional-commits.json'), true),
            json_decode($this->filesystem->readfile(__DIR__ . '/../../res/rules/strict.json'), true),
        );

        $target = __DIR__;
        $dirs = [];

        do {
            $dirs[] = $target;
            $target = dirname($target);
        } while(!in_array($target, $dirs, true));

        foreach($dirs as $dir) {
            $target = $dir . '/.php-commit-lint.json';
            if($this->filesystem->exists($target)) {
                $configuration = array_merge(
                    $configuration,
                    json_decode($this->filesystem->readfile($target), true)
                );
                break;
            }
        }

        return $configuration;
    }

}
