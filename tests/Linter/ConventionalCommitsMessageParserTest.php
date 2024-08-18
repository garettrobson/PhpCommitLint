<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use GarettRobson\PhpCommitLint\Linter\ConventionalCommitsMessageParser;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversClass(ConventionalCommitsMessageParser::class)]
final class ConventionalCommitsMessageParserTest extends TestCase
{
    public function testParseCorrectMessage(): void
    {
        $testMessage = 'feat(user-auth): add multi-factor authentication';

        $messageParser = new ConventionalCommitsMessageParser($testMessage);

        $this->assertSame('feat', $messageParser->get('type'));
        $this->assertSame('user-auth', $messageParser->get('scope'));
        $this->assertSame('add multi-factor authentication', $messageParser->get('description'));
        $this->assertFalse($messageParser->has('breaking'));
    }
}
