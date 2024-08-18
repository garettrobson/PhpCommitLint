<?php

declare(strict_types=1);

namespace GarettRobson\PhpCommitLint\Linter;

use GarettRobson\PhpCommitLint\Linter\Message;

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

    public function addError(string $error)
    {
        $this->errors[] = $error;
        return $this;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    abstract public function performValidation(Message $message): self;

}
