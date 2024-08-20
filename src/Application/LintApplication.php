<?php

declare(strict_types=1);

namespace GarettRobson\PhpCommitLint\Application;

use GarettRobson\PhpCommitLint\Command\ConfigCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use GarettRobson\PhpCommitLint\Command\LintCommand;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;

class LintApplication extends Application
{
    public function __construct()
    {
        parent::__construct('git-commit-lint', '0.0.0');
        $this->addCommands([
            new LintCommand(),
            new ConfigCommand(),
        ]);
    }

    protected function configureIO(InputInterface $input, OutputInterface $output): void
    {
        parent::configureIO($input, $output);
        $output->getFormatter()->setStyle('error', new OutputFormatterStyle('red'));

    }
}
