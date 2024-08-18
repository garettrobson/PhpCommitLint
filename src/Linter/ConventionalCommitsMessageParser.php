<?php

declare(strict_types=1);

namespace GarettRobson\PhpCommitLint\Linter;

class ConventionalCommitsMessageParser extends MessageParser
{
    public function __construct(string $message, int $flags = PREG_UNMATCHED_AS_NULL)
    {
        $pattern = file_get_contents(__DIR__ . '/../../res/message-parse.regex.pattern');
        parent::__construct($message, $pattern, $flags);
    }
}
