<?php

declare(strict_types=1);

namespace GarettRobson\PhpCommitLint\Message;

class ConventionalCommitsMessageParser extends PatternLoadingMessageParser
{
    public function __construct()
    {
        parent::__construct(__DIR__ . '/../../res/conventional-commits.regex.pattern');
    }
}
