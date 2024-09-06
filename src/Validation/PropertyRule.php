<?php

declare(strict_types=1);

namespace GarettRobson\PhpCommitLint\Validation;

abstract class PropertyRule extends Rule
{
    protected string $property;
    protected string $errorMessage;

    public function __construct(
        protected \stdClass $definition
    ) {
        parent::__construct($definition);
    }

    protected function getRequiredProperties(): array
    {
        return array_merge(
            parent::getRequiredProperties(),
            [
                'property' => 'string',
                'errorMessage' => 'string',
            ]
        );
    }
}
