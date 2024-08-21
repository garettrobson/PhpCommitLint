<?php

declare(strict_types=1);

namespace GarettRobson\PhpCommitLint\Validation;

use GarettRobson\PhpCommitLint\Message\Message;

class PropertyExistenceRule extends PropertyRule
{
    public function __construct(
        protected string $property,
        protected bool $exists = true,
        protected string $errorMessage = 'Required %s'
    ) {
    }

    public function performValidation(Message $message): self
    {
        if($this->exists ^ $message->has($this->property)) {
            $this->addError(
                $this->errorMessage,
                $this->property
            );
        }

        return $this;
    }
}
