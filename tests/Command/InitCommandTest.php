<?php

declare(strict_types=1);

namespace GarettRobson\PhpCommitLint\Tests\Command;

use GarettRobson\PhpCommitLint\Application\PhpCommitLintApplication;
use GarettRobson\PhpCommitLint\Command\ConfigCommand;
use GarettRobson\PhpCommitLint\Command\InitCommand;
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
#[CoversClass(InitCommand::class)]
class InitCommandTest extends TestCase
{
    public function testNonInteractiveExecute(): void
    {
        $application = new PhpCommitLintApplication();

        $command = $application->find('init');
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            [
                'target-directory' => __DIR__.'/res/tmp',
                '--rule-set' => ['formatting50-72'],
                '--yes' => true,
                '--no-local' => true,
                '--no-home' => true,
            ],
            [
                'verbosity' => OutputInterface::VERBOSITY_VERY_VERBOSE,
            ]
        );

        $commandTester->assertCommandIsSuccessful();

        $expected = file_get_contents(__DIR__.'/res/test/.php-commit-lint-reference.json');
        $actual = file_get_contents(__DIR__.'/res/tmp/.php-commit-lint.json');
        unlink(__DIR__.'/res/tmp/.php-commit-lint.json');

        $this->assertSame($expected, $actual);
    }

    public function testInteractiveExecute(): void
    {
        $application = new PhpCommitLintApplication();

        $command = $application->find('init');

        $commandTester = new CommandTester($command);

        $commandTester->setInputs(['.', __DIR__.'/res/tmp', 'formatting50-72', 'yes']);
        $commandTester->execute([], [
            'interactive' => true,
            'verbosity' => OutputInterface::VERBOSITY_VERY_VERBOSE,
        ]);

        $commandTester->assertCommandIsSuccessful();

        $expected = file_get_contents(__DIR__.'/res/test/.php-commit-lint-reference.json');
        $actual = file_get_contents(__DIR__.'/res/tmp/.php-commit-lint.json');
        unlink(__DIR__.'/res/tmp/.php-commit-lint.json');

        $this->assertSame($expected, $actual);
    }

    public function testInteractiveQuit(): void
    {
        $application = new PhpCommitLintApplication();

        $command = $application->find('init');

        $commandTester = new CommandTester($command);

        $commandTester->setInputs(['q']);
        $commandTester->execute([], ['interactive' => true]);

        $output = $commandTester->getDisplay();

        $this->assertStringContainsString('[ERROR] Setup stopped', $output);
    }
}
