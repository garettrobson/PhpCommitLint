<?php

declare(strict_types=1);

namespace GarettRobson\PhpCommitLint\Message;

/**
 * The real star of the message parser inheritance, specifies the regex file to
 * load as the pattern.
 */
class ConventionalCommitsMessageParser extends PatternFileMessageParser
{
    public function __construct()
    {
        parent::__construct(__DIR__.'/../../res/conventional-commits.regex.pattern');
    }
}
