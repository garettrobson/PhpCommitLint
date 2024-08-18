<?php

declare(strict_types=1);

namespace GarettRobson\PhpCommitLint\Tests\Command;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Console\Tester\CommandTester;
use GarettRobson\PhpCommitLint\Linter\MessageParser;
use GarettRobson\PhpCommitLint\Command\LintMessageCommand;
use GarettRobson\PhpCommitLint\Application\LintApplication;
use GarettRobson\PhpCommitLint\Linter\PatternLoadingMessageParser;
use GarettRobson\PhpCommitLint\Linter\ConventionalCommitsMessageParser;

#[CoversClass(LintMessageCommand::class)]
#[CoversClass(LintApplication::class)]
#[CoversClass(ConventionalCommitsMessageParser::class)]
#[CoversClass(MessageParser::class)]
#[CoversClass(PatternLoadingMessageParser::class)]
class LintMessageCommandTest extends TestCase
{
    public function testExecute(): void
    {
        $application = new LintApplication();

        $command = $application->find('message:lint');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'file' => __DIR__ . '/res/test/message.txt',
        ]);

        $commandTester->assertCommandIsSuccessful();

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('[OK] Message lint successful', $output);
    }
}
