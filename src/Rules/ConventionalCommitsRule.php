<?php

declare(strict_types=1);

namespace GarettRobson\PhpCommitLint\Rules;

use GarettRobson\PhpCommitLint\Linter\Rule;
use GarettRobson\PhpCommitLint\Linter\Message;

class ConventionalCommitsRule extends Rule
{
    public function __construct(
        protected array $typesAllowed = [
            'fix',
            'feat',
            'build',
            'chore',
            'ci',
            'docs',
            'style',
            'refactor',
            'perf',
            'test',
        ],
        protected string $scopePattern = '/^[\w-]+$/',
        protected string $descriptionPattern = '/^[A-Z0-9].*$/',
    ) {
    }

    public function performValidation(Message $message): self
    {
        if(!$message->hasType()) {
            $this->addError('Message does not have a type');
        } elseif (!in_array($message->getType(), $this->typesAllowed, true)) {
            $this->addError(sprintf(
                'Type of %s not allowed, must be one of: %s',
                $message->getType(),
                implode(', ', $this->typesAllowed)
            ));
        }

        if (
            $message->hasScope() &&
            !preg_match($this->scopePattern, $message->getScope())
        ) {
            $this->addError(sprintf('Scope "%s" does not conform to expected pattern: %s', $message->getScope(), $this->scopePattern));
        }

        if (
            $message->hasDescription() &&
            !preg_match($this->descriptionPattern, $message->getDescription())
        ) {
            $this->addError(sprintf('Description "%s" does not conform to expected pattern: %s', $message->getDescription(), $this->descriptionPattern));
        }

        return $this;
    }
}
