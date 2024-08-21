<?php

declare(strict_types=1);

namespace GarettRobson\PhpCommitLint\Command;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ConfigCommand extends PhpCommitLintCommand
{
    public function __construct()
    {
        parent::__construct('config');
    }

    protected function configure(): void
    {
        parent::configure();
        $this
            ->setDescription('Configuration tool')
            ->setHelp('Configuration tool for php-commit-lint')
            ->addOption(
                'using',
                'u',
                InputOption::VALUE_NONE,
                'Display information about configured rules'
            )
            ->addOption(
                'rule-sets',
                'r',
                InputOption::VALUE_NONE,
                'Display information about rule sets'
            )
            ->addOption(
                'types',
                't',
                InputOption::VALUE_NONE,
                'Display information about rule types'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        $io = new SymfonyStyle($input, $output);

        $io->title('PHP Commit Lint: Config');

        parent::execute($input, $output);

        $executeDefault = true;

        if ($input->getOption('types')) {

            $executeDefault = false;

            $io->section('Types');

            foreach ($this->validationConfiguration->getTypes() as $typeName => $typeClass) {

                $io->writeln(sprintf(
                    ' <info>[%s]</info> %s',
                    $typeName,
                    $typeClass,
                ));

            }
        }

        if ($input->getOption('rule-sets')) {

            $executeDefault = false;

            $io->section('Rule sets');

            foreach($this->validationConfiguration->getRuleSets() as $ruleSetName => $ruleSet) {

                $io->writeln(sprintf('<comment>%s:</comment>', $ruleSetName));

                foreach ($ruleSet as $ruleName => $rule) {

                    $io->writeln(sprintf(
                        ' <info>[%s]</info> %s (%s)',
                        $rule->name,
                        $rule->type,
                        implode(
                            ', ',
                            array_map(
                                fn($parameter) => sprintf(
                                    '<comment>%s</comment>',
                                    json_encode($parameter)
                                ),
                                $rule->parameters ?? [],
                            )
                        )
                    ));

                    if ($io->getVerbosity() >= $io::VERBOSITY_VERY_VERBOSE) {
                        $io->writeln(
                            sprintf(' - <info>Included:</info> <text>%s</text>', $rule->included),
                            $io::VERBOSITY_VERY_VERBOSE
                        );
                    }

                }

            }

        }

        if($input->getOption('using') || $executeDefault) {

            $io->section('Using rules');

            foreach ($this->validationConfiguration->getRules() as $ruleName => $rule) {

                $io->writeln(sprintf(
                    '<info>[%s]</info> %s(%s)',
                    $ruleName,
                    $rule->type,
                    implode(
                        ', ',
                        array_map(
                            fn($parameter) => sprintf(
                                '<comment>%s</comment>',
                                json_encode($parameter)
                            ),
                            $rule->parameters ?? [],
                        )
                    )
                ));

                if ($io->getVerbosity() >= $io::VERBOSITY_VERY_VERBOSE) {
                    $io->writeln(sprintf(
                        ' - <info>Class:</info> <text>%s</text>',
                        $rule->class
                    ));
                    $io->writeln(sprintf(
                        ' - <info>Included:</info> <text>%s</text>',
                        $rule->included
                    ));
                    $io->writeln(sprintf(
                        ' - <info>From:</info> <text>%s</text>',
                        $rule->from
                    ));
                }

            }

        }

        return self::SUCCESS;
    }
}
