<?php

declare(strict_types=1);

namespace GarettRobson\PhpCommitLint\Linter;

class ConventionalCommitsMessageParser extends PatternLoadingMessageParser
{
    public function __construct(string $message)
    {
        parent::__construct(
            $message,
            __DIR__ . '/../../res/message-parse.regex.pattern'
        );
    }
}
