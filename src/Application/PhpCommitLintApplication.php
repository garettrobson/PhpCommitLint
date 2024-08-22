<?php

declare(strict_types=1);

namespace GarettRobson\PhpCommitLint\Application;

use GarettRobson\PhpCommitLint\Command\ConfigCommand;
use GarettRobson\PhpCommitLint\Command\ConfigSetupCommand;
use GarettRobson\PhpCommitLint\Command\LintCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PhpCommitLintApplication extends Application
{
    public function __construct()
    {
        parent::__construct('git-commit-lint', 'alpha');
        $this->addCommands([
            new LintCommand(),
            new ConfigCommand(),
            new ConfigSetupCommand(),
        ]);
    }

    protected function configureIO(InputInterface $input, OutputInterface $output): void
    {
        parent::configureIO($input, $output);
        $output->getFormatter()->setStyle('error', new OutputFormatterStyle('red'));
        $output->getFormatter()->setStyle('text', new OutputFormatterStyle('gray'));
    }
}
