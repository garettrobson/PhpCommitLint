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

        $this->validateRuleProperties(
            $requiredProperties,
            $optionalProperties
        );

        $this->validateDefinition(
            $definition,
            $requiredProperties,
            $optionalProperties
        );

        if ($this->mapProperties) {
            foreach ((array) $definition as $property => $value) {
                $this->{$property} = $value;
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

    /**
     * @param array<string, null|string> $requiredProperties
     * @param array<string, null|string> $optionalProperties
     */
    protected function validateRuleProperties(array $requiredProperties, array $optionalProperties): void
    {
        // Required and Optional properties should all exist on this object
        foreach ($requiredProperties as $property => $type) {
            if (!property_exists($this, $property)) {
                throw new \RuntimeException(sprintf(
                    'Incorrectly configured class %s missing property %s which expects %s',
                    static::class,
                    $property,
                    $type,
                ));
            }
        }

        foreach ($optionalProperties as $property => $type) {
            if (!property_exists($this, $property)) {
                throw new \RuntimeException(sprintf(
                    'Incorrectly configured class %s missing property %s which expects %s',
                    static::class,
                    $property,
                    $type,
                ));
            }
            if (!(new \ReflectionProperty('User', 'name'))->isInitialized($this)) {
                throw new \RuntimeException(sprintf(
                    'Incorrectly configured class %s uninitialized optional property %s which expects %s',
                    static::class,
                    $property,
                    $type,
                ));
            }
        }
    }

    /**
     * @param array<string, null|string> $requiredProperties
     * @param array<string, null|string> $optionalProperties
     */
    protected function validateDefinition(\stdClass $definition, array $requiredProperties, array $optionalProperties): void
    {
        foreach ((array) $definition as $property => $value) {
            if (isset($requiredProperties[$property])) {
                if ($this->getType($value) !== $requiredProperties[$property]) {
                    throw new \RuntimeException(sprintf(
                        "Rule definition contradiction in class %s required property %s of type %s, received %s:\n%s",
                        static::class,
                        $property,
                        $requiredProperties[$property],
                        $this->getType($value),
                        json_encode($definition, JSON_PRETTY_PRINT),
                    ));
                }
            } elseif (isset($optionalProperties[$property])) {
                if ($this->getType($value) !== $optionalProperties[$property]) {
                    throw new \RuntimeException(sprintf(
                        "Rule definition contradiction in class %s optional property %s of type %s, received %s:\n%s",
                        static::class,
                        $property,
                        $optionalProperties[$property],
                        $this->getType($value),
                        json_encode($definition, JSON_PRETTY_PRINT),
                    ));
                }
            } else {
                throw new \RuntimeException(sprintf(
                    "Rule definition error in class %s found unexpected property %s found on rule:\n%s",
                    static::class,
                    $property,
                    json_encode($definition, JSON_PRETTY_PRINT),
                ));
            }
        }
    }

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
