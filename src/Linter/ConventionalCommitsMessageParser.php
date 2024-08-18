<?php

declare(strict_types=1);

namespace GarettRobson\PhpCommitLint\Linter;

class ConventionalCommitsMessageParser extends PatternLoadingMessageParser
{
    public function __construct()
    {
        parent::__construct(__DIR__ . '/../../res/conventional-commits.regex.pattern');
    }
}
