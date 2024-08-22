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
class ConfigCommandTest extends TestCase
{
    public function testExecute(): void
    {
        $application = new PhpCommitLintApplication();

        $command = $application->find('config');
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        $commandTester->assertCommandIsSuccessful();
        $output = $commandTester->getDisplay();

        $this->assertStringContainsString('[line-length]', $output);
        $this->assertStringContainsString('[summary-no-leading-spaces]', $output);
    }
}
