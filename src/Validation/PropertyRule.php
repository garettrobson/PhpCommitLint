<?php

declare(strict_types=1);

namespace GarettRobson\PhpCommitLint\Validation;

abstract class PropertyRule extends Rule
{
    public function __construct(
        protected string $property,
    ) {}
}
