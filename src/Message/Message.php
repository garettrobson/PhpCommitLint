<?php

declare(strict_types=1);

namespace GarettRobson\PhpCommitLint\Message;

use RuntimeException;
use Psr\Container\ContainerInterface;

class Message implements ContainerInterface
{
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
     * @throws NotFoundExceptionInterface  No match was found for **this** identifier.
     *
     * @return mixed Entry.
     */
    public function get(string $id)
    {
        if(!$this->has($id)) {
            throw new MessagePropertyNotFoundException(sprintf(
                'Attempting to access unset property %s of %s',
                $id,
                __METHOD__
            ));
        }
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

    public function set(string $id, $variable)
    {
        $this->matches[$id] = $variable;
        return $this;
    }

    public function __call(string $function, array $arguments)
    {
        preg_match('/^(?<operation>get|has|set)(?<variable>[A-Z]\w*)$/', $function, $matches);

        if(!$matches) {
            throw new RuntimeException(sprintf(
                'Call to unhandled method in %s: %s',
                __METHOD__,
                $function
            ));
        }

        $variable = lcfirst($matches['variable']);
        switch($matches['operation']) {
            case 'has':
                return $this->has($variable);
            case 'get':
                return $this->get($variable);
            case 'set':
                $count = count($arguments);
                if($count !== 1) {
                    throw new RuntimeException(sprintf(
                        'Expected 1 argument in %s, received %s',
                        __METHOD__,
                        $count
                    ));
                }
                return $this->set($variable, $arguments[0]);
        }
    }
}
