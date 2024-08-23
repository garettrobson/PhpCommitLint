<?php

declare(strict_types=1);

namespace GarettRobson\PhpCommitLint\Validation;

use GarettRobson\PhpCommitLint\Message\Message;

class PropertyExistenceRule extends PropertyRule
{
    public function __construct(
        protected string $property,
        protected bool $positiveCheck = true,
        protected string $errorMessage = 'Required property %s missing',
    ) {}

    public function performValidation(Message $message): self
    {
        if ($this->positiveCheck ^ $message->has($this->property)) {
            $this->addError(
                $this->errorMessage,
                $this->property
            );
        }

        return $this;
    }
}
