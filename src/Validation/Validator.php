<?php

declare(strict_types=1);

namespace GarettRobson\PhpCommitLint\Validation;

use GarettRobson\PhpCommitLint\Message\Message;

class Validator
{
    public function __construct(
        protected array $rules = []
    ) {
    }

    public function validate(Message $message)
    {
        $errors = [];
        foreach($this->rules as $rule) {
            array_push($errors, ...$rule->validate($message));
        }
        return $errors;
    }
}
