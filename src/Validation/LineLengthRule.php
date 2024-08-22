<?php

declare(strict_types=1);

namespace GarettRobson\PhpCommitLint\Validation;

use GarettRobson\PhpCommitLint\Message\Message;

class LineLengthRule extends Rule
{
    /**
     * @param array<int> $lineLengths
     */
    public function __construct(
        protected array $lineLengths = [50, 0],
        protected int $defaultLineLength = 72,
    ) {}

    public function performValidation(Message $message): self
    {
        $messageArray = explode("\n", $message->get('message'));
        foreach ($messageArray as $index => $line) {
            $lineLength = $this->lineLengths[$index] ?? $this->defaultLineLength;
            if (strlen($line) > $lineLength) {
                $this->addError(
                    'Line %s is %s characters long, exceeds %s limit',
                    $index + 1,
                    strlen($line),
                    $lineLength,
                );
            }
        }

        return $this;
    }
}
