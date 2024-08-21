<?php

declare(strict_types=1);

namespace GarettRobson\PhpCommitLint\Message;

use RuntimeException;

/**
 * A git commit message parser, but it loads the pattern from a file
 */
abstract class PatternFileMessageParser extends MessageParser
{
    public function __construct(string $patternPath, int $flags = PREG_UNMATCHED_AS_NULL)
    {
        $pattern = file_get_contents($patternPath);
        if($pattern === false) {
            throw new RuntimeException(sprintf(
                'Failed to load pattern file %s',
                $patternPath
            ));
        }
        parent::__construct($pattern, $flags);
    }
}
