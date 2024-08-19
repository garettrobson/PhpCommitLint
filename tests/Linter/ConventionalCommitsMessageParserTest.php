<?php

declare(strict_types=1);

namespace GarettRobson\PhpCommitLint\Tests\Linter;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use GarettRobson\PhpCommitLint\Message\Message;
use GarettRobson\PhpCommitLint\Message\ConventionalCommitsMessageParser;

#[CoversClass(ConventionalCommitsMessageParser::class)]
#[CoversClass(Message::class)]
final class ConventionalCommitsMessageParserTest extends TestCase
{
    public function testParseSimpleMessage(): void
    {
        $testMessage = 'Initial commit';

        $messageParser = new ConventionalCommitsMessageParser();
        $message = $messageParser->parseMessage($testMessage);

        $this->assertSame($testMessage, $message->get('type'));
        $this->assertFalse($message->has('scope'));
        $this->assertFalse($message->has('breaking'));
        $this->assertSame('', $message->get('description'));
    }

    public function testParseCorrectMessage(): void
    {
        $testMessage = 'feat(user-auth): add multi-factor authentication';

        $messageParser = new ConventionalCommitsMessageParser();
        $message = $messageParser->parseMessage($testMessage);

        $this->assertSame('feat', $message->get('type'));
        $this->assertSame('user-auth', $message->get('scope'));
        $this->assertFalse($message->has('breaking'));
        $this->assertSame('add multi-factor authentication', $message->get('description'));
    }

    public function testParseCompleteMessage(): void
    {
        $testMessage = <<<TEST_MESSAGE
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

        $messageParser = new ConventionalCommitsMessageParser($testMessage);
        $message = $messageParser->parseMessage($testMessage);

        $this->assertSame('feat', $message->get('type'));
        $this->assertSame('user-auth', $message->get('scope'));
        $this->assertSame('!', $message->get('breaking'));
        $this->assertSame('add multi-factor authentication', $message->get('description'));
        $this->assertStringEndsWith($message->get('body'), $testMessage);
    }
}
