<?php

declare(strict_types=1);

namespace GarettRobson\PhpCommitLint\Command;

use Symfony\Component\Filesystem\Path;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\AnsiColorMode;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class ConfigSetupCommand extends PhpCommitLintCommand
{
    protected bool $executeDefault = true;

    public function __construct()
    {
        parent::__construct('config:setup');
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
        $helper = $this->getHelper('question');

        $io->title('PHP Commit Lint: Setup');

        $targetDirectory = $this->askTargetDirectory($input, $output, $io);

        // askTargetDirectory returns null to signal termination
        if($targetDirectory === null) {
            $this->stop($io);
        }

        $io->writeln(sprintf(
            '<comment>Target directory</comment> <info>%s</info>',
            $targetDirectory,
        ));

        $ruleSets = $this->askRuleSets($input, $output, $io);

        // askRuleSets returns null to signal termination
        if ($ruleSets === null) {
            $this->stop($io);
        }

        $data = [
            'using' => $ruleSets,
        ];

        $json = json_encode($data, JSON_PRETTY_PRINT) . PHP_EOL;

        $io->writeln('<comment>Preview JSON</comment>');
        $io->writeln(sprintf(
            '<info>%s</info>',
            $json,
        ));

        $targetFile = $targetDirectory . DIRECTORY_SEPARATOR . '.php-commit-lint.json';
        $confirm = $this->askConfirm($input, $output, $io, $targetFile);

        if(!$confirm) {
            return $this->stop($io);
        }

        $this->filesystem->dumpFile($targetFile, $json);
        $io->success(sprintf(
            'New local override file %s created successfully',
            $targetFile,
        ));

        return self::SUCCESS;
    }

    /**
     * Undocumented function
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param SymfonyStyle $io
     * @return string|null
     */
    public function askTargetDirectory(InputInterface $input, OutputInterface $output, SymfonyStyle $io): ?string
    {

        if($targetDirectory = $input->getArgument('target-directory')) {
            return is_string($targetDirectory) ? $targetDirectory : null;
        }

        $helper = $this->getHelper('question');

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

        /** @var QuestionHelper $helper */
        $answer = $helper->ask($input, $output, $question);

        switch ($answer) {
            case 'q':
                return null;
            case '.':
                $answer = $io->askQuestion(new Question(
                    'Specify where the .php-commit-lint.json file should be created',
                ));
                return is_string($answer) ? $answer : '';
            default:
                return $directoryChoices[$answer];
        }
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param SymfonyStyle $io
     * @return ?array<string>
     */
    public function askRuleSets(InputInterface $input, OutputInterface $output, SymfonyStyle $io): ?array
    {
        if($ruleSets = $input->getOption('rule-set') ?? []) {
            return is_array($ruleSets) ? $ruleSets : null;
        }

        $helper = $this->getHelper('question');

        $ruleSetChoices = array_keys(get_object_vars($this->validationConfiguration->getRuleSets()));
        array_unshift($ruleSetChoices, '');
        unset($ruleSetChoices[0]);

        $question = new ChoiceQuestion(
            'Which rule sets would you like to use? (Enter numbers separated by commas',
            $ruleSetChoices,
        );
        $question->setMultiselect(true);

        /** @var QuestionHelper $helper */
        /** @var array<string> $answer */
        $answer = (array)$helper->ask($input, $output, $question);
        return $answer;
    }

    public function askConfirm(InputInterface $input, OutputInterface $output, SymfonyStyle $io, string $targetFile): bool
    {

        if($input->getOption('yes')) {
            return true;
        }

        $question = new ConfirmationQuestion(
            sprintf(
                'Write the JSON above to <comment>%s</comment>?',
                $targetFile,
            ),
            true
        );
        $question->setAutocompleterValues(['yes', 'no']);

        $answer = $io->askQuestion($question);

        return !!$answer;
    }

    public function stop(SymfonyStyle $io): int
    {
        $io->error('Setup stopped');
        return self::FAILURE;
    }

}
