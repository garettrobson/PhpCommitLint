<?php

declare(strict_types=1);

namespace GarettRobson\PhpCommitLint\Tests\Message;

use GarettRobson\PhpCommitLint\Message\PatternFileMessageParser;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Exception\IOException;

/**
 * @internal
 */
#[CoversClass(PatternFileMessageParser::class)]
final class PatternFileMessageParserTest extends TestCase
{
    public function testNonExistentFile(): void
    {
        $this->expectException(IOException::class);

        new PatternFileMessageParser(
            'non-existent.json'
        );
    }
}
