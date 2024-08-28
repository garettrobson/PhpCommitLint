<?php

declare(strict_types=1);

namespace GarettRobson\PhpCommitLint\Tests\Message;

use GarettRobson\PhpCommitLint\Message\Message;
use GarettRobson\PhpCommitLint\Message\PatternFileMessageParser;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Exception\IOException;

/**
 * @internal
 */
#[CoversClass(Message::class)]
#[CoversClass(PatternFileMessageParser::class)]
final class PatternFileMessageParserTest extends TestCase
{
    public function testParseSimpleMessage(): void
    {
        $testMessage = 'Initial commit';

        $messageParser = new PatternFileMessageParser(__DIR__.'/../../res/conventional-commits.regex.pattern');
        $message = $messageParser->parseMessage($testMessage);

        $this->assertSame($testMessage, $message->get('type'));
        $this->assertFalse($message->has('scope'));
        $this->assertFalse($message->has('breaking'));
        $this->assertSame('', $message->get('description'));
        $this->assertFalse($message->has('body'));
    }

    public function testParseCorrectMessage(): void
    {
        $testMessage = 'feat(user-auth): add multi-factor authentication';

        $messageParser = new PatternFileMessageParser(__DIR__.'/../../res/conventional-commits.regex.pattern');
        $message = $messageParser->parseMessage($testMessage);

        $this->assertSame('feat', $message->get('type'));
        $this->assertSame('user-auth', $message->get('scope'));
        $this->assertFalse($message->has('breaking'));
        $this->assertSame('add multi-factor authentication', $message->get('description'));
        $this->assertFalse($message->has('body'));
    }

    public function testParseCompleteMessage(): void
    {
        $testMessage = <<<'TEST_MESSAGE'
feat(user-auth)!: add multi-factor authentication

This commit introduces multi-factor authentication for user accounts.
The new feature allows users to enable 2FA using either SMS or
authenticator apps.

Key changes:
- Added new database table for storing 2FA settings
- Implemented SMS verification flow
- Added support for TOTP-based authenticator apps
- Updated user settings UI to include 2FA options

Breaking changes:
The user login API now returns a new field 'requires_2fa'
which must be handled by clients.

Closes: #123
Reviewed-by: Alice
BREAKING CHANGE: User login API response structure changed
TEST_MESSAGE;

        $messageParser = new PatternFileMessageParser(__DIR__.'/../../res/conventional-commits.regex.pattern');
        $message = $messageParser->parseMessage($testMessage);

        $this->assertSame('feat', $message->get('type'));
        $this->assertSame('user-auth', $message->get('scope'));
        $this->assertSame('!', $message->get('breaking'));
        $this->assertSame('add multi-factor authentication', $message->get('description'));
        $this->assertTrue($message->has('body'));
        $this->assertNotEmpty($message->get('body'));
        $this->assertStringEndsWith($message->get('body'), $testMessage);
    }

    public function testNonExistentFile(): void
    {
        $this->expectException(IOException::class);

        new PatternFileMessageParser(
            'non-existent.json'
        );
    }
}
