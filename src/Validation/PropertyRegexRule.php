<?php

declare(strict_types=1);

namespace GarettRobson\PhpCommitLint\Validation;

use GarettRobson\PhpCommitLint\Message\Message;

class PropertyRegexRule extends PropertyRule
{
    protected string $property;
    protected string $pattern;
    protected bool $positiveCheck = true;
    protected string $errorMessage = 'Unexpected %s of value %s, does not conform to pattern: %s';

    public function __construct(
        protected \stdClass $definition
    ) {
        parent::__construct($definition);
    }

    public function performValidation(Message $message): self
    {
        if (
            $message->has($this->property)
            && (preg_match($this->pattern, $message->get($this->property)) ^ $this->positiveCheck)
        ) {
            $this->addMessage(
                $this->errorMessage,
                $this->property,
                $message->get($this->property),
                $this->pattern,
            );
        }

        return $this;
    }

    protected function getRequiredProperties(): array
    {
        return array_merge(
            parent::getRequiredProperties(),
            [
                'property' => 'string',
                'pattern' => 'string',
                'positiveCheck' => 'boolean',
                'errorMessage' => 'string',
            ]
        );
    }
}
