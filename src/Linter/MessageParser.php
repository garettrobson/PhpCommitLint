<?php

namespace GarettRobson\PhpCommitLint\Linter;

class MessageParser
{

    protected ?string $type = null;
    protected ?string $scope = null;
    protected ?bool $breaking = null;
    protected ?string $description = null;
    protected ?string $body = null;

    /**
     * Parse a commit message using a specified regex pattern
     *
     * @param string $message Commit message to parse
     * @param string $pattern Regular Expression pattern to use
     * @param string $flags Regular Expression flags, see preg_match flags
     *   (default PREG_UNMATCHED_AS_NULL)
     */
    public function __construct(string $message, string $pattern, int $flags = PREG_UNMATCHED_AS_NULL)
    {
        preg_match($pattern, $message, $matches, $flags);
        var_dump($matches);
    }
}
