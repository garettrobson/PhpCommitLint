<?php

declare(strict_types=1);

namespace GarettRobson\PhpCommitLint\Message;

use Psr\Container\ContainerInterface;

/**
 * Represents a parsed git commit message, mostly by holding an array of regex
 * matches, which it makes accessible via ContainerInterface's get and has
 * methods.
 *
 * Notes: I'm unsure if message is necessary as it only offers access to the
 * underlying array of matches. Similarly unsure if it should make use of
 * ArrayAccess, ArrayObject, or even Iterator.
 */
class Message implements ContainerInterface, \JsonSerializable
{
    /**
     * @var array<string, string>
     */
    protected array $matches;

    /**
     * @param array<string,mixed> $matches
     */
    public function __construct(
        array $matches
    ) {
        /** @var array<string, string> $matches */
        $matches = array_filter(
            $matches,
            fn ($match) => is_string($match),
            ARRAY_FILTER_USE_KEY
        );

        $this->matches = $matches;
    }

    /**
     * Finds a match of the message by its identifier and returns it.
     *
     * @param string $id identifier of the match to look for
     *
     * @return string entry
     *
     * @throws MessagePropertyNotFoundException no match was found for **this** identifier
     */
    public function get(string $id): string
    {
        if (!$this->has($id)) {
            throw new MessagePropertyNotFoundException(sprintf(
                'Attempting to access unset property %s of %s',
                $id,
                __METHOD__
            ));
        }

        return $this->matches[$id];
    }

    /**
     * Returns true if the message can return a match for the given identifier.
     * Returns false otherwise.
     *
     * `has($id)` returning true does not mean that `get($id)` will not throw an exception.
     * It does however mean that `get($id)` will not throw a `NotFoundExceptionInterface`.
     *
     * @param string $id identifier of the match to look for
     */
    public function has(string $id): bool
    {
        return isset($this->matches[$id]);
    }

    public function set(string $id, string $variable): self
    {
        $this->matches[$id] = $variable;

        return $this;
    }

    public function jsonSerialize(): mixed
    {
        return $this->matches;
    }

    /** @return array<null|string> */
    public function getMatches(): array
    {
        return $this->matches;
    }
}
