<?php

declare(strict_types=1);

namespace GarettRobson\PhpCommitLint\Validation;

use GarettRobson\PhpCommitLint\Message\Message;

class Validator
{
    protected array $rules = [];

    public function addRule(Rule $rule): self
    {
        $this->rules[$rule::class] = $rule;
        return $this;
    }

    public function getRule(string $class): Rule
    {
        return isset($this->rules[$class]) ? $this->rules[$class] : false;
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
