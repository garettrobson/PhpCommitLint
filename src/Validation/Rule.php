<?php

declare(strict_types=1);

namespace GarettRobson\PhpCommitLint\Validation;

use GarettRobson\PhpCommitLint\Message\Message;

abstract class Rule
{
    /** @var array<string> */
    protected array $errors = [];

    /**
     * @return array<string>
     */
    final public function validate(Message $message): array
    {
        return $this
            ->resetErrors()
            ->performValidation($message)
            ->getErrors()
        ;
    }

    public function resetErrors(): self
    {
        $this->errors = [];

        return $this;
    }

    /**
     * @param null|bool|float|int|string ...$arguments
     */
    public function addError(string $errorMessage, ...$arguments): self
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
    public function getErrors(): array
    {
        return $this->errors;
    }

    abstract public function performValidation(Message $message): self;
}
