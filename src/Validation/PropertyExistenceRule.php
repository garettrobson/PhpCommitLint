<?php

declare(strict_types=1);

namespace GarettRobson\PhpCommitLint\Validation;

use GarettRobson\PhpCommitLint\Message\Message;

class PropertyExistenceRule extends PropertyRule
{
    protected bool $positiveCheck = true;
    protected string $errorMessage = 'Required property %s missing';

    public function __construct(
        protected \stdClass $definition
    ) {
        parent::__construct($definition);
    }

    public function performValidation(Message $message): self
    {
        if ($this->positiveCheck ^ $message->has($this->property)) {
            $this->addMessage(
                $this->errorMessage,
                $this->property
            );
        }

        return $this;
    }

    protected function getRequiredProperties(): array
    {
        return array_merge(
            parent::getRequiredProperties(),
            [
                'positiveCheck' => 'boolean',
            ]
        );
    }
}
