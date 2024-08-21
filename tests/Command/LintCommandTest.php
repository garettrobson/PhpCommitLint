<?php

declare(strict_types=1);

namespace GarettRobson\PhpCommitLint\Tests\Command;

use RuntimeException;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use GarettRobson\PhpCommitLint\Message\Message;
use GarettRobson\PhpCommitLint\Command\LintCommand;
use Symfony\Component\Console\Tester\CommandTester;
use GarettRobson\PhpCommitLint\Validation\Validator;
use GarettRobson\PhpCommitLint\Command\ConfigCommand;
use GarettRobson\PhpCommitLint\Message\MessageParser;
use GarettRobson\PhpCommitLint\Validation\LineLengthRule;
use GarettRobson\PhpCommitLint\Validation\PropertySetRule;
use GarettRobson\PhpCommitLint\Application\PhpCommitLintApplication;
use GarettRobson\PhpCommitLint\Validation\PropertyRegexRule;
use GarettRobson\PhpCommitLint\Message\PatternFileMessageParser;
use GarettRobson\PhpCommitLint\Validation\PropertyExistenceRule;
use GarettRobson\PhpCommitLint\Validation\ValidatorConfiguration;
use GarettRobson\PhpCommitLint\Message\ConventionalCommitsMessageParser;

#[CoversClass(LintCommand::class)]
#[CoversClass(PhpCommitLintApplication::class)]
#[CoversClass(ConventionalCommitsMessageParser::class)]
#[CoversClass(MessageParser::class)]
#[CoversClass(PatternFileMessageParser::class)]
#[CoversClass(Message::class)]
#[CoversClass(Validator::class)]
#[CoversClass(LineLengthRule::class)]
#[CoversClass(PropertyExistenceRule::class)]
#[CoversClass(PropertySetRule::class)]
#[CoversClass(ConfigCommand::class)]
#[CoversClass(ValidatorConfiguration::class)]
#[CoversClass(PropertyRegexRule::class)]
class LintCommandTest extends TestCase
{
    public function testExecuteWithValidFile(): void
    {
        $application = new PhpCommitLintApplication();

        $command = $application->find('lint');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'file' => __DIR__ . '/res/test/conventional-commits-valid.txt',
        ]);

        $commandTester->assertCommandIsSuccessful();
        $output = $commandTester->getDisplay();

        $this->assertStringContainsString('[OK] Commit message passed linting', $output);
    }

    public function testExecuteWithInvalidFile(): void
    {
        $application = new PhpCommitLintApplication();

        $command = $application->find('lint');
        $commandTester = new CommandTester($command);

        $this->expectException(RuntimeException::class);

        $commandTester->execute([
            'file' => __DIR__ . '/file-does-not-exist.txt',
        ]);
    }
}
