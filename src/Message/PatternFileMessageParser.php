<?php

declare(strict_types=1);

namespace GarettRobson\PhpCommitLint\Message;

use Symfony\Component\Filesystem\Filesystem;

/**
 * A git commit message parser, but it loads the pattern from a file.
 */
class PatternFileMessageParser extends MessageParser
{
    public function __construct(string $patternFile, int $flags = PREG_UNMATCHED_AS_NULL)
    {
        $pattern = (new Filesystem())->readfile($patternFile);
        parent::__construct($pattern, $flags);
    }
}
