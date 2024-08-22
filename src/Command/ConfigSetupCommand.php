<?php

declare(strict_types=1);

namespace GarettRobson\PhpCommitLint\Command;

use RuntimeException;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
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
            ->setHelp('Setup a new .php-commit-lint file in your project')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        parent::execute($input, $output);
        $io = new SymfonyStyle($input, $output);
        $helper = $this->getHelper('question');

        $io->title('PHP Commit Lint: Setup');

        $targetDir = false;

        $autoDetectDirectories = array_filter([
            'Found a .php-commit-lint.json file in %s, should we override it?' => $this->findLocalFile('.php-commit-lint.json', false),
            'Found a .git repo in %s, should we create a .php-commit-lint.json file here?' => $this->findLocalFile('.git', false),
            'Found a composer.json file in %s, should we create a .php-commit-lint.json file here?' => $this->findLocalFile('composer.json', false),
            'Current directory is %s, should we create a .php-commit-lint.json file here?' => getcwd(),
        ]);

        foreach($autoDetectDirectories as $questionText => $directory) {
            $directory = $directory;

            if($targetDir = $io->askQuestion(new ConfirmationQuestion(
                sprintf($questionText, $directory)
            )) ? $directory : false) {
                break;
            }
        }

        if (!$targetDir) {
            $targetDir = $io->askQuestion(new Question(
                'Specify where the .php-commit-lint.json file should be created',
            ));
        }

        $ruleSetNames = array_keys(get_object_vars($this->validationConfiguration->getRuleSets()));
        $selectedRuleSets = [];
        do {
            $io->writeln(sprintf(
                '<info>Current rule sets:</info> %s',
                $selectedRuleSets ?
                    implode(', ', array_map(
                        fn ($ruleName) => sprintf('<text>%s</text>', $ruleName),
                        $selectedRuleSets
                    )) :
                    '<comment>-none-</comment>',
            ));
            $io->writeln(sprintf(
                '<info>Available rule sets:</info> %s',
                $ruleSetNames ?
                    implode(', ', array_map(
                        fn ($ruleName) => sprintf('<text>%s</text>', $ruleName),
                        $ruleSetNames
                    )) :
                    '<comment>-none-</comment>',
            ));

            $question = new Question('Please choose a rule set to add, to stop adding rule sets leave empty');
            $question->setAutocompleterValues($ruleSetNames);
            $ruleSetName = $io->askQuestion($question);

            if(!$ruleSetName) {
                break;
            } elseif (in_array($ruleSetName, $ruleSetNames, true)) {
                $selectedRuleSets[] = $ruleSetName;
                $index = array_search($ruleSetName, $ruleSetNames);
                unset($ruleSetNames[$index]);
            } elseif(is_string($ruleSetName)) {
                $io->warning(sprintf(
                    'There is no rule set with the name "%s"',
                    $ruleSetName,
                ));
            } else {
                throw new RuntimeException(sprintf(
                    'Unexpected rule set name %s',
                    json_encode($ruleSetName),
                ));
            }

        } while(true);

        $data = [
            'using' => $selectedRuleSets,
        ];

        $json = json_encode($data, JSON_PRETTY_PRINT) . PHP_EOL;

        $io->section('Review');
        $io->writeln(sprintf('<text>%s</text>', $json));

        $targetFile = $targetDir . DIRECTORY_SEPARATOR . '.php-commit-lint.json';
        $confirm = $io->askQuestion(new ConfirmationQuestion(
            sprintf(
                'Write the JSON above to <comment>%s</comment>?',
                $targetFile,
            )
        ));

        if($confirm) {
            $this->filesystem->dumpFile($targetFile, $json);
            $io->success(sprintf(
                'New local override file %s created successfully',
                $targetFile,
            ));
        } else {
            $io->error('Setup stopped');
            return self::FAILURE;
        }

        return self::SUCCESS;
    }

}
