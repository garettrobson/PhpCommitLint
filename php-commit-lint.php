<?php

include 'vendor/autoload.php';

use GarettRobson\PhpCommitLint\Application\LintApplication;
use GarettRobson\PhpCommitLint\Command\LintMessageCommand;

$application = new LintApplication();

$application->addCommands([
    new LintMessageCommand()
]);

$application->setDefaultCommand('message:lint', true);

$application->run();
