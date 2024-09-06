<?php

declare(strict_types=1);

namespace GarettRobson\PhpCommitLint\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

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
            $this->displayTypes($io);
        }

        if ($input->getOption('rule-sets')) {
            $executeDefault = false;
            $this->displayRuleSets($io);
        }

        if ($input->getOption('using') || $executeDefault) {
            $this->displayUsing($io);
        }

        return self::SUCCESS;
    }

    protected function displayTypes(SymfonyStyle $io): void
    {
        $io->section('Types');

        foreach ((array) $this->validationConfiguration->getTypes() as $typeName => $typeClass) {
            $classColor = class_exists($typeClass, true) ? 'text' : 'error';

            $io->writeln(sprintf(
                ' <info>[%s]</info> <%3$s>%s</%3$s>',
                $typeName,
                $typeClass,
                $classColor,
            ));

            if ($io->getVerbosity() >= $io::VERBOSITY_VERBOSE) {
                foreach ($typeClass::getRequiredProperties() ?? [] as $propertyName => $propertyType) {
                    $io->writeln(sprintf(
                        '  - <comment>%s</comment>: %s',
                        $propertyName,
                        $propertyType,
                    ));
                }
                foreach ($typeClass::getOptionalProperties() ?? [] as $propertyName => $propertyType) {
                    $io->writeln(sprintf(
                        '  - <info>%s</info>: %s',
                        $propertyName,
                        $propertyType,
                    ));
                }
            }
        }
    }

    protected function displayRuleSets(SymfonyStyle $io): void
    {
        $io->section('Rule sets');

        foreach ((array) $this->validationConfiguration->getRuleSets() as $ruleSetName => $ruleSet) {
            $io->writeln(sprintf('<comment>%s:</comment>', $ruleSetName));

            foreach ($ruleSet as $ruleName => $rule) {
                $this->displayRule($io, $rule);
            }
        }
    }

    protected function displayUsing(SymfonyStyle $io): void
    {
        $io->section('Using rules');

        foreach ($this->validationConfiguration->getRules() as $ruleName => $rule) {
            $this->displayRule($io, $rule);
        }
    }

    protected function displayRule(SymfonyStyle $io, \stdClass $rule): void
    {
        $validTypes = array_keys(get_object_vars($this->validationConfiguration->getTypes()));

        $typeColor = in_array(
            $rule->type,
            $validTypes,
            true
        ) ? 'text' : 'error';

        $io->writeln(sprintf(
            ' <info>[%s]</info> <%3$s>%s</%3$s>',
            $rule->name,
            $rule->type,
            $typeColor,
        ));

        if ($io->getVerbosity() >= $io::VERBOSITY_VERBOSE) {
            $systemProperties = ['class', 'from', 'included'];

            $properties = (array) $rule;
            $properties = array_intersect_key($properties, array_flip($systemProperties));

            foreach ($properties as $key => $property) {
                $io->writeln(
                    sprintf(
                        ' - <info>%s:</info> <text>%s</text>',
                        $key,
                        $property,
                    )
                );
            }
        }

        if ($io->getVerbosity() >= $io::VERBOSITY_VERY_VERBOSE) {
            $systemProperties = ['name', 'type', 'included', 'class', 'from'];

            $properties = (array) $rule;
            $properties = array_diff_key($properties, array_flip($systemProperties));

            foreach ($properties as $key => $property) {
                $property = is_object($property) || is_array($property) ?
                    json_encode($property) :
                    $property;

                $io->writeln(
                    sprintf(
                        ' - <comment>%s:</comment> <text>%s</text>',
                        $key,
                        $property,
                    )
                );
            }
        }
    }
}
