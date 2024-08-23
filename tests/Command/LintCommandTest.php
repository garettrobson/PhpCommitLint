<?php

declare(strict_types=1);

namespace GarettRobson\PhpCommitLint\Tests\Command;

use GarettRobson\PhpCommitLint\Application\PhpCommitLintApplication;
use GarettRobson\PhpCommitLint\Command\ConfigCommand;
use GarettRobson\PhpCommitLint\Command\ConfigSetupCommand;
use GarettRobson\PhpCommitLint\Command\LintCommand;
use GarettRobson\PhpCommitLint\Message\ConventionalCommitsMessageParser;
use GarettRobson\PhpCommitLint\Message\Message;
use GarettRobson\PhpCommitLint\Message\MessageParser;
use GarettRobson\PhpCommitLint\Message\PatternFileMessageParser;
use GarettRobson\PhpCommitLint\Validation\LineLengthRule;
use GarettRobson\PhpCommitLint\Validation\PropertyExistenceRule;
use GarettRobson\PhpCommitLint\Validation\PropertyRegexRule;
use GarettRobson\PhpCommitLint\Validation\PropertySetRule;
use GarettRobson\PhpCommitLint\Validation\Validator;
use GarettRobson\PhpCommitLint\Validation\ValidatorConfiguration;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @internal
 */
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
#[CoversClass(ConfigSetupCommand::class)]
class LintCommandTest extends TestCase
{
    public function testExecuteNoArgumentFile(): void
    {
        $application = new PhpCommitLintApplication();

        $command = $application->find('lint');
        $commandTester = new CommandTester($command);

        $this->expectException(\RuntimeException::class);
        $commandTester->execute(
            [],
            [
                'verbosity' => OutputInterface::VERBOSITY_VERY_VERBOSE,
            ]
        );
    }

    public function testExecuteWithValidMessage(): void
    {
        $application = new PhpCommitLintApplication();

        $command = $application->find('lint');
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            [
                'file' => __DIR__.'/res/test/conventional-commits-valid.txt',
            ],
            [
                'verbosity' => OutputInterface::VERBOSITY_VERY_VERBOSE,
            ]
        );

        $commandTester->assertCommandIsSuccessful();
        $output = $commandTester->getDisplay();

        $this->assertStringContainsString('[OK] Commit message passed linting', $output);
    }

    public function testExecuteWithInvalidMessage(): void
    {
        $application = new PhpCommitLintApplication();

        $command = $application->find('lint');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'file' => __DIR__.'/res/test/conventional-commits-invalid.txt',
        ]);

        $output = $commandTester->getDisplay();

        $this->assertStringContainsString('[ERROR] Commit message failed linting', $output);
    }

    public function testExecuteNonExistentFile(): void
    {
        $application = new PhpCommitLintApplication();

        $command = $application->find('lint');
        $commandTester = new CommandTester($command);

        $this->expectException(\RuntimeException::class);

        $commandTester->execute([
            'file' => __DIR__.'/file-does-not-exist.txt',
        ]);
    }
}
