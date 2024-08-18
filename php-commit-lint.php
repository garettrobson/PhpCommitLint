<?php

include 'vendor/autoload.php';

use GarettRobson\PhpCommitLint\Application\LintApplication;

$application = new LintApplication();

$application->run();
