<?php

declare(strict_types=1);

namespace GarettRobson\PhpCommitLint\Linter;

abstract class MessageParser
{
    /**
     * Parse a commit message using a specified regex pattern
     *
     * @param string $message Commit message to parse
     * @param string $pattern Regular Expression pattern to use
     * @param int $flags Regular Expression flags, see preg_match flags
     *   (default PREG_UNMATCHED_AS_NULL)
     */
    public function __construct(
        protected string $pattern,
        protected int $flags = PREG_UNMATCHED_AS_NULL
    ) {
    }

    public function parseMessage(string $message): Message
    {
        preg_match($this->pattern, $message, $matches, $this->flags);
        return new Message($matches);
    }

}
