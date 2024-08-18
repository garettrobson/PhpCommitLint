<?php

declare(strict_types=1);

namespace GarettRobson\PhpCommitLint\Tests\Linter;

use \RuntimeException;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use GarettRobson\PhpCommitLint\Linter\Message;
use GarettRobson\PhpCommitLint\Linter\MessagePropertyNotFoundException;

#[CoversClass(Message::class)]
final class MessageTest extends TestCase
{
    public function testSimpleMessage(): void
    {
        $message = new Message([
            'foo' => 'bar',
            'baz' => 'qux',
        ]);

        $this->assertTrue($message->has('foo'));
        $this->assertTrue($message->has('baz'));
        $this->assertFalse($message->has('fail'));
        $this->assertSame('bar', $message->get('foo'));
        $this->assertSame('qux', $message->get('baz'));
    }

    public function testAccessUnset(): void
    {
        $message = new Message([]);

        $this->expectException(MessagePropertyNotFoundException::class);

        $message->get('foo');
    }

    public function testSuccessfulGetter(): void
    {
        $message = new Message([
            'foo' => 'bar',
        ]);

        $this->assertSame('bar', $message->getFoo());
    }

    public function testInvalidGetter(): void
    {
        $message = new Message([]);

        $this->expectException(MessagePropertyNotFoundException::class);

        $message->getFoo();
    }

    public function testSuccessfulSetter(): void
    {
        $message = new Message([]);

        $this->assertFalse($message->has('foo'));

        $message->setFoo('bar');

        $this->assertTrue($message->has('foo'));
        $this->assertSame('bar', $message->get('foo'));
    }

    public function testInvalidSetter(): void
    {
        $message = new Message([]);

        $this->expectException(RuntimeException::class);

        $message->setFoo();
    }

    public function testExcessiveSetter(): void
    {
        $message = new Message([]);

        $this->expectException(RuntimeException::class);

        $message->setFoo('bar', 'baz');
    }

    public function testHasser(): void
    {
        $message = new Message([]);

        $this->assertFalse($message->has('foo'));

        $message->setFoo('bar');

        $this->assertTrue($message->hasFoo());
        $this->assertFalse($message->hasNonExistent());
    }

    public function testFailedCall()
    {
        $message = new Message([]);

        $this->expectException(RuntimeException::class);

        $message->nonExistent();
    }

}
