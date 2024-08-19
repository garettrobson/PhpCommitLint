<?php

declare(strict_types=1);

namespace GarettRobson\PhpCommitLint\Validation;

use GarettRobson\PhpCommitLint\Message\Message;

class PropertyRegexRule extends PropertyRule
{
    public function __construct(
        protected string $property,
        protected string $pattern,
        protected string $errorMessage = 'Unexpected %s of value %s, does not conform to pattern: %s'
    ) {
        parent::__construct($property);
    }

    public function performValidation(Message $message): self
    {
        if (
            $message->has($this->property) &&
            !preg_match($this->pattern, $message->get($this->property))
        ) {
            $this->addError(
                $this->errorMessage,
                $this->property,
                $message->get($this->property),
                $this->pattern
            );
        }

        return $this;
    }
}
