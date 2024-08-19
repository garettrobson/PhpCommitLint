<?php

declare(strict_types=1);

namespace GarettRobson\PhpCommitLint\Tests\Command;

use RuntimeException;
use PHPUnit\Framework\TestCase;
use GarettRobson\PhpCommitLint\Linter\Rule;
use PHPUnit\Framework\Attributes\CoversClass;
use GarettRobson\PhpCommitLint\Linter\Message;
use GarettRobson\PhpCommitLint\Linter\Validator;
use Symfony\Component\Console\Tester\CommandTester;
use GarettRobson\PhpCommitLint\Linter\MessageParser;
use GarettRobson\PhpCommitLint\Rules\LineLengthRule;
use GarettRobson\PhpCommitLint\Rules\PropertySetRule;
use GarettRobson\PhpCommitLint\Command\LintMessageCommand;
use GarettRobson\PhpCommitLint\Rules\PropertyRequiredRule;
use GarettRobson\PhpCommitLint\Application\LintApplication;
use GarettRobson\PhpCommitLint\Linter\PatternLoadingMessageParser;
use GarettRobson\PhpCommitLint\Linter\ConventionalCommitsMessageParser;

#[CoversClass(LintMessageCommand::class)]
#[CoversClass(LintApplication::class)]
#[CoversClass(ConventionalCommitsMessageParser::class)]
#[CoversClass(MessageParser::class)]
#[CoversClass(PatternLoadingMessageParser::class)]
#[CoversClass(Message::class)]
#[CoversClass(Rule::class)]
#[CoversClass(Validator::class)]
#[CoversClass(LineLengthRule::class)]
#[CoversClass(PropertyRequiredRule::class)]
#[CoversClass(PropertySetRule::class)]
class LintMessageCommandTest extends TestCase
{
    public function testExecuteWithValidFile(): void
    {
        $application = new LintApplication();

        $command = $application->find('message:lint');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'file' => __DIR__ . '/res/test/message.txt',
        ]);

        $commandTester->assertCommandIsSuccessful();
        $output = $commandTester->getDisplay();

        $this->assertStringContainsString('[OK] Commit message passed linting', $output);
    }

    public function testExecuteWithInvalidFile(): void
    {
        $application = new LintApplication();

        $command = $application->find('message:lint');
        $commandTester = new CommandTester($command);

        $this->expectException(RuntimeException::class);
        $commandTester->execute([
            'file' => __DIR__ . '/file-does-not-exist.txt',
        ]);
    }
}
