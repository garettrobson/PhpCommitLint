<?php

declare(strict_types=1);

namespace GarettRobson\PhpCommitLint\Message;

use RuntimeException;
use Psr\Container\ContainerInterface;

class Message implements ContainerInterface
{
    /**
     * @param array<string, string> $matches
     */
    public function __construct(
        protected array $matches
    ) {
        $matches = array_filter($matches, "is_string", ARRAY_FILTER_USE_KEY);
    }

    /**
     * Finds a match of the message by its identifier and returns it.
     *
     * @param string $id Identifier of the match to look for.
     *
     * @throws MessagePropertyNotFoundException  No match was found for **this** identifier.
     *
     * @return string Entry.
     */
    public function get(string $id): string
    {
        if(!$this->has($id)) {
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
     * @param string $id Identifier of the match to look for.
     *
     * @return bool
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
}
