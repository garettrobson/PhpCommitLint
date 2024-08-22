<?php

declare(strict_types=1);

namespace GarettRobson\PhpCommitLint\Message;

/**
 * Define a git commit message parser as a regex expression.
 */
abstract class MessageParser
{
    /**
     * Parse a commit message using a specified regex pattern.
     *
     * @param string        $pattern Regular Expression pattern to use
     * @param 0|256|512|768 $flags   Regular Expression flags, see preg_match flags
     *                               (default PREG_UNMATCHED_AS_NULL)
     */
    public function __construct(
        protected string $pattern,
        protected int $flags = PREG_UNMATCHED_AS_NULL,
    ) {}

    public function parseMessage(string $message): Message
    {
        preg_match($this->pattern, $message, $matches, $this->flags);

        // @var array<string, string> $matches
        return new Message($matches);
    }
}
