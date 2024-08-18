<?php

declare(strict_types=1);

namespace GarettRobson\PhpCommitLint\Application;

use Symfony\Component\Console\Application;
use GarettRobson\PhpCommitLint\Command\LintMessageCommand;

class LintApplication extends Application
{

    public function __construct()
    {
        parent::__construct('git-commit-lint', '0.0.0');
        $this->addCommands([
            new LintMessageCommand()
        ]);
        $this->setDefaultCommand('message:lint', true);
    }
}
