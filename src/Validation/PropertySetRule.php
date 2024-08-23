<?php

declare(strict_types=1);

namespace GarettRobson\PhpCommitLint\Validation;

use GarettRobson\PhpCommitLint\Message\Message;

class PropertySetRule extends PropertyRule
{
    protected string $property;

    /** @var array<string> */
    protected array $set = [];
    protected string $errorMessage = 'Unexpected %s of value %s, must be one of; %s';

    public function __construct(
        protected \stdClass $definition
    ) {
        parent::__construct($definition);
    }

    public function performValidation(Message $message): self
    {
        if (
            $message->has($this->property)
            && !in_array(trim($message->get($this->property)), $this->set, true)
        ) {
            $this->addError(
                $this->errorMessage,
                $this->property,
                $message->get($this->property),
                json_encode($this->set, JSON_UNESCAPED_SLASHES),
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
                'set' => 'array',
                'errorMessage' => 'string',
            ]
        );
    }
}
