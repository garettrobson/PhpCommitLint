<?php

declare(strict_types=1);

namespace GarettRobson\PhpCommitLint\Validation;

use GarettRobson\PhpCommitLint\Message\Message;

class Validator
{
    /**
     * @param array<Rule> $rules
     */
    public function __construct(
        protected array $rules = []
    ) {
    }

    /**
     * @param Message $message
     * @return array<string>
     */
    public function validate(Message $message): array
    {
        $errors = [];
        foreach($this->rules as $rule) {
            array_push($errors, ...$rule->validate($message));
        }
        return $errors;
    }
}
