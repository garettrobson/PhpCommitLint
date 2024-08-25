<?php

declare(strict_types=1);

namespace GarettRobson\PhpCommitLint\Validation;

use GarettRobson\PhpCommitLint\Message\Message;

class LineLengthRule extends Rule
{
    /** @var array<int> */
    protected array $lineLengths = [50, 0];
    protected int $defaultLineLength = 72;
    protected string $pattern;
    protected bool $positiveCheck = true;

    public function __construct(
        protected \stdClass $definition
    ) {
        parent::__construct($definition);
    }

    public function performValidation(Message $message): self
    {
        $messageArray = explode("\n", $message->get('message'));
        foreach ($messageArray as $index => $line) {
            $lineLength = $this->lineLengths[$index] ?? $this->defaultLineLength;
            if (
                (strlen($line) > $lineLength)
                && (preg_match($this->pattern, $line) ^ $this->positiveCheck)
            ) {
                $this->addMessage(
                    'Line %s is %s characters long, exceeds %s limit',
                    $index + 1,
                    strlen($line),
                    $lineLength,
                );
            }
        }

        return $this;
    }

    protected function getRequiredProperties(): array
    {
        return array_merge(
            parent::getRequiredProperties(),
            [
                'lineLengths' => 'array',
                'defaultLineLength' => 'integer',
                'pattern' => 'string',
                'positiveCheck' => 'boolean',
            ]
        );
    }
}
