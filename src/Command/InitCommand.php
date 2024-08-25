<?php

declare(strict_types=1);

namespace GarettRobson\PhpCommitLint\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Path;

class InitCommand extends PhpCommitLintCommand
{
    public function __construct()
    {
        parent::__construct('init');
    }

    /**
     * Undocumented function.
     */
    protected function askTargetDirectory(InputInterface $input, OutputInterface $output, SymfonyStyle $io): ?string
    {
        if ($targetDirectory = $input->getArgument('target-directory')) {
            return is_string($targetDirectory) ? $targetDirectory : null;
        }

        $directoryChoices = array_filter([
            'override' => $this->findLocalFile('.php-commit-lint.json', false),
            'repository' => $this->findLocalFile('.git', false),
            'project' => $this->findLocalFile('composer.json', false),
            'current' => getcwd(),
            'home' => Path::getHomeDirectory(),
            '.' => 'Enter a path manually',
            'q' => 'Quit',
        ]);

        $question = new ChoiceQuestion(
            'Where would you like the new override file to be created?',
            $directoryChoices,
            '0'
        );

        $answer = $io->askQuestion($question);

        switch ($answer) {
            case 'q':
                return null;

            case '.':
                $callback = function (string $userInput): array {
                    // Strip any characters from the last slash to the end of the string
                    // to keep only the last directory and generate suggestions for it
                    $inputPath = preg_replace('%(/|^)[^/]*$%', '$1', $userInput);
                    $inputPath = '' === $inputPath ? '.' : $inputPath;

                    if (!is_string($inputPath)) {
                        throw new \RuntimeException(sprintf(
                            'Auto suggest callback expected a string input path, received %s',
                            gettype($inputPath),
                        ));
                    }

                    // CAUTION - this example code allows unrestricted access to the
                    // entire filesystem. In real applications, restrict the directories
                    // where files and dirs can be found
                    $foundFilesAndDirs = scandir($inputPath) ?: [];

                    return array_map(function (string $dirOrFile) use ($inputPath): string {
                        return $inputPath.$dirOrFile;
                    }, $foundFilesAndDirs);
                };

                $question = new Question(sprintf(
                    'What directory should the new .php-commit-lint.json override file should be created, can be relative to <comment>%s</comment>',
                    getcwd(),
                ));
                $question->setAutocompleterCallback($callback);

                $answer = $io->askQuestion($question);

                if (null === $answer) {
                    return null;
                }
                if (!is_string($answer)) {
                    throw new \RuntimeException(sprintf(
                        'Expected user input path to be string, received %s',
                        gettype($answer),
                    ));
                }
                if ($this->filesystem->isAbsolutePath($answer)) {
                    return $answer;
                }
                if ($cwd = getcwd()) {
                    return Path::makeAbsolute($answer, $cwd);
                }

                return null;

            default:
                return $directoryChoices[$answer];
        }
    }

    /**
     * @return ?array<string>
     */
    protected function askRuleSets(InputInterface $input, OutputInterface $output, SymfonyStyle $io): ?array
    {
        if ($ruleSets = $input->getOption('rule-set') ?? []) {
            return is_array($ruleSets) ? $ruleSets : null;
        }

        $ruleSetChoices = array_keys(get_object_vars($this->validationConfiguration->getRuleSets()));
        array_unshift($ruleSetChoices, '');
        unset($ruleSetChoices[0]);

        $question = new ChoiceQuestion(
            'Which rule sets would you like to use? (Enter numbers separated by commas)',
            $ruleSetChoices,
        );
        $question->setMultiselect(true);

        $answer = $io->askQuestion($question);

        return is_array($answer) ? $answer : null;
    }

    protected function askConfirm(InputInterface $input, OutputInterface $output, SymfonyStyle $io, string $targetFile): bool
    {
        if ($input->getOption('yes')) {
            return true;
        }

        $question = new ConfirmationQuestion(
            sprintf(
                'Write the override JSON file to <comment>%s</comment>?',
                $targetFile,
            ),
            true
        );
        $question->setAutocompleterValues(['yes', 'no']);

        $answer = $io->askQuestion($question);

        return (bool) $answer;
    }

    protected function stop(SymfonyStyle $io): int
    {
        $io->error('Setup stopped');

        return self::FAILURE;
    }

    protected function configure(): void
    {
        parent::configure();
        $this
            ->setDescription('Setup wizard')
            ->setHelp('Guided setup for a new .php-commit-lint overrides file')
            ->addArgument(
                'target-directory',
                InputArgument::OPTIONAL,
                'Specify the directory in which to setup the new overrides file'
            )
            ->addOption(
                'rule-set',
                'r',
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Specify the directory in which to setup the new overrides file',
                []
            )
            ->addOption(
                'yes',
                'y',
                InputOption::VALUE_NONE,
                'Accept the final confirmation automatically'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        parent::execute($input, $output);
        $io = new SymfonyStyle($input, $output);

        $io->title('PHP Commit Lint: Setup');

        $targetDirectory = $this->askTargetDirectory($input, $output, $io);

        // askTargetDirectory returns null to signal termination
        if (null === $targetDirectory) {
            return $this->stop($io);
        }

        $io->writeln(sprintf(
            '<comment>Target directory</comment> <info>%s</info>',
            $targetDirectory,
        ));

        $ruleSets = $this->askRuleSets($input, $output, $io);

        // askRuleSets returns null to signal termination
        if (null === $ruleSets) {
            $this->stop($io);
        }

        $data = [
            'using' => $ruleSets,
        ];

        $json = json_encode($data, JSON_PRETTY_PRINT).PHP_EOL;

        $io->writeln('<comment>Preview JSON</comment>');
        $io->writeln(sprintf(
            '<info>%s</info>',
            $json,
        ));

        $targetFile = $targetDirectory.DIRECTORY_SEPARATOR.'.php-commit-lint.json';
        $confirm = $this->askConfirm($input, $output, $io, $targetFile);

        if (!$confirm) {
            return $this->stop($io);
        }

        $this->filesystem->dumpFile($targetFile, $json);
        $io->success(sprintf(
            'New local override file %s created successfully',
            $targetFile,
        ));

        return self::SUCCESS;
    }
}
