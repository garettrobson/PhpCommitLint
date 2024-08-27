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
        usort(
            $rules,
            fn ($ruleA, $ruleB) => $ruleA->compare($ruleB)
        );
    }

    /**
     * @return array<string>
     */
    public function validate(Message $message): array
    {
        $validationMessages = [];
        foreach ($this->rules as $rule) {
            $messages = $rule->validate($message);
            if ($rule->isPass() && $messages) {
                return [];
            }
            array_push($validationMessages, ...$messages);
        }

        return $validationMessages;
    }
}
