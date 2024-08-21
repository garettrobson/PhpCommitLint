<?php

declare(strict_types=1);

namespace GarettRobson\PhpCommitLint\Command;

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
            ->setHelp(<<<HELP
Configuration tool for php-commit-lint
HELP)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        $io = new SymfonyStyle($input, $output);

        $io->title('PHP Commit Lint: Config');

        parent::execute($input, $output);

        $rules = $this->validationConfiguration->getRules();

        $io->section('Using rules');

        foreach ($rules as $ruleName => $rule) {
            $io->writeln(sprintf(
                '<info>[%s]</info> %s (%s)',
                $rule->name,
                $rule->type,
                implode(
                    ', ',
                    array_map(
                        fn ($parameter) => sprintf(
                            '<comment>%s</comment>',
                            json_encode($parameter)
                        ),
                        $rule->parameters ?? [],
                    )
                )
            ));
            $io->writeln(
                sprintf(' - <info>Class:</info> <text>%s</text>', $rule->class),
                $io::VERBOSITY_VERBOSE
            );
            $io->writeln(
                sprintf(' - <info>Included:</info> <text>%s</text>', $rule->included),
                $io::VERBOSITY_VERBOSE
            );
        }

        return self::SUCCESS;
    }
}
