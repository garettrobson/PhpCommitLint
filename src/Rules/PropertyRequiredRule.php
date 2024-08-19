<?php

declare(strict_types=1);

namespace GarettRobson\PhpCommitLint\Rules;

use GarettRobson\PhpCommitLint\Linter\Message;

class PropertyRequiredRule extends PropertyRule
{
    public function __construct(
        protected string $property,
        protected string $errorMessage = 'Required %s'
    ) {
    }

    public function performValidation(Message $message): self
    {
        if(!$message->has($this->property)) {
            $this->addError(
                $this->errorMessage,
                $this->property
            );
        }

        return $this;
    }
}
