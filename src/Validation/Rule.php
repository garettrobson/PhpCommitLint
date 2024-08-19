<?php

declare(strict_types=1);

namespace GarettRobson\PhpCommitLint\Validation;

use GarettRobson\PhpCommitLint\Message\Message;

abstract class Rule
{
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

    public function addError(string $errorMessage, ...$arguments)
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

    public function getErrors()
    {
        return $this->errors;
    }

    abstract public function performValidation(Message $message): self;

}
