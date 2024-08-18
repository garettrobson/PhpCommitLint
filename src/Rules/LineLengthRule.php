<?php

declare(strict_types=1);

namespace GarettRobson\PhpCommitLint\Rules;

use GarettRobson\PhpCommitLint\Linter\Rule;
use GarettRobson\PhpCommitLint\Linter\Message;

class LineLengthRule extends Rule
{
    public function __construct(
        protected int $titleMaximumLength = 50,
        protected int $bodyMaximumLength = 72,
    ) {
    }

    public function setTitleMaximumLength(int $titleMaximumLength): self
    {
        $this->titleMaximumLength = $titleMaximumLength;
        return $this;
    }

    public function getTitleMaximumLength(): int
    {
        return $this->titleMaximumLength;
    }

    public function setBodyMaximumLength(int $bodyMaximumLength): self
    {
        $this->bodyMaximumLength = $bodyMaximumLength;
        return $this;
    }

    public function getBodyMaximumLength(): int
    {
        return $this->bodyMaximumLength;
    }

    public function performValidation(Message $message): self
    {
        if (
            $message->hasTitle() &&
            (strlen($message->getTitle()) > $this->titleMaximumLength)
        ) {
            $this->addError('Title exceeds %s characters', $this->titleMaximumLength);
        }

        if (
            $message->hasBody() &&
            $body = explode("\n", $message->getBody())
        ) {
            foreach($body as $index => $line) {
                if(strlen($line) > $this->bodyMaximumLength) {
                    $this->addError('Line %s line exceeds %s characters', $index + 1, $this->titleMaximumLength);
                }
            }
        }

        return $this;
    }
}
