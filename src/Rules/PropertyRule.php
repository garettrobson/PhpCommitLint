<?php

declare(strict_types=1);

namespace GarettRobson\PhpCommitLint\Rules;

use GarettRobson\PhpCommitLint\Linter\Rule;

abstract class PropertyRule extends Rule
{
    public function __construct(
        protected string $property,
    ) {
    }
}
