<?php

declare(strict_types=1);

namespace GarettRobson\PhpCommitLint\Linter;

use Psr\Container\ContainerInterface;

abstract class MessageParser implements ContainerInterface
{
    protected array $matches = [];

    /**
     * Parse a commit message using a specified regex pattern
     *
     * @param string $message Commit message to parse
     * @param string $pattern Regular Expression pattern to use
     * @param int $flags Regular Expression flags, see preg_match flags
     *   (default PREG_UNMATCHED_AS_NULL)
     */
    public function __construct(string $message, string $pattern, int $flags = PREG_UNMATCHED_AS_NULL)
    {
        preg_match($pattern, $message, $this->matches, $flags);
    }

    /**
     * Finds a match of the message by its identifier and returns it.
     *
     * @param string $id Identifier of the match to look for.
     *
     * @throws NotFoundExceptionInterface  No match was found for **this** identifier.
     * @throws ContainerExceptionInterface Error while retrieving the match.
     *
     * @return mixed Entry.
     */
    public function get(string $id)
    {
        return $this->matches[$id] ?? null;
    }

    /**
     * Returns true if the message can return a match for the given identifier.
     * Returns false otherwise.
     *
     * `has($id)` returning true does not mean that `get($id)` will not throw an exception.
     * It does however mean that `get($id)` will not throw a `NotFoundExceptionInterface`.
     *
     * @param string $id Identifier of the match to look for.
     *
     * @return bool
     */
    public function has(string $id): bool
    {
        return isset($this->matches[$id]);
    }
}
