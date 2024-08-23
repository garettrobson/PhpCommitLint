<?php

declare(strict_types=1);

namespace GarettRobson\PhpCommitLint\Validation;

use GarettRobson\PhpCommitLint\Message\Message;

abstract class Rule
{
    /** @var array<string> */
    protected array $errors = [];

    protected string $type;
    protected string $name;
    protected string $included;
    protected string $from;
    protected string $class;

    /** @var array<string, string> */
    protected array $requiredProperties = [
        'type' => 'string',
        'name' => 'string',
        'included' => 'string',
        'from' => 'string',
        'class' => 'string',
    ];

    /** @var array<string, string> */
    protected array $optionalProperties = [
    ];

    protected bool $mapProperties = true;

    protected function __construct(
        protected \stdClass $definition
    ) {
        $requiredProperties = $this->getRequiredProperties();

        $optionalProperties = $this->getOptionalProperties();

        foreach ((array) $definition as $property => $value) {
            if (isset($requiredProperties[$property])) {
                if ($this->getType($value) !== $requiredProperties[$property]) {
                    throw new \RuntimeException(sprintf(
                        "Rule definition expected required property %s of type %s, received %s:\n%s",
                        $property,
                        $requiredProperties[$property],
                        $this->getType($value),
                        json_encode($definition, JSON_PRETTY_PRINT),
                    ));
                }
            } elseif (isset($optionalProperties[$property])) {
                if ($this->getType($value) !== $optionalProperties[$property]) {
                    throw new \RuntimeException(sprintf(
                        "Rule definition expected optional property %s of type %s, received %s:\n%s",
                        $property,
                        $optionalProperties[$property],
                        $this->getType($value),
                        json_encode($definition, JSON_PRETTY_PRINT),
                    ));
                }
            } else {
                throw new \RuntimeException(sprintf(
                    "Rule definition found unexpected property %s found on rule:\n%s",
                    $property,
                    json_encode($definition, JSON_PRETTY_PRINT),
                ));
            }

            if ($this->mapProperties) {
                if (property_exists($this, $property)) {
                    $this->{$property} = $value;
                } elseif (isset($requiredProperties[$property])) {
                    throw new \RuntimeException(sprintf(
                        "Rule definition required property %s does not exist on class %s:\n%s",
                        $property,
                        __CLASS__,
                        json_encode($definition, JSON_PRETTY_PRINT),
                    ));
                } elseif (isset($requiredProperties[$property])) {
                    throw new \RuntimeException(sprintf(
                        "Rule definition optional property %s does not exist on class %s:\n%s",
                        $property,
                        __CLASS__,
                        json_encode($definition, JSON_PRETTY_PRINT),
                    ));
                } else {
                    throw new \RuntimeException(sprintf(
                        "Rule definition property %s is unexpected on class %s:\n%s",
                        $property,
                        __CLASS__,
                        json_encode($definition, JSON_PRETTY_PRINT),
                    ));
                }
            }
        }
    }

    /**
     * @return array<string>
     */
    final public function validate(Message $message): array
    {
        return $this
            ->resetMessages()
            ->performValidation($message)
            ->getMessages()
        ;
    }

    public function resetMessages(): self
    {
        $this->errors = [];

        return $this;
    }

    /**
     * @param null|bool|float|int|string ...$arguments
     */
    public function addMessage(string $errorMessage, ...$arguments): self
    {
        $arguments = array_map(
            fn ($val) => sprintf('<comment>%s</comment>', $val),
            $arguments,
        );

        $this->errors[] = sprintf(
            $errorMessage,
            ...$arguments
        );

        return $this;
    }

    /**
     * @return array<string>
     */
    public function getMessages(): array
    {
        return $this->errors;
    }

    abstract public function performValidation(Message $message): self;

    /** @return array<null|string> */
    protected function getRequiredProperties(): array
    {
        return $this->requiredProperties;
    }

    /** @return array<null|string> */
    protected function getOptionalProperties(): array
    {
        return $this->optionalProperties;
    }

    protected function getType(mixed $mixed): string
    {
        if (is_object($mixed)) {
            return get_class($mixed);
        }

        return gettype($mixed);
    }
}
