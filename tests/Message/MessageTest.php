<?php

declare(strict_types=1);

namespace GarettRobson\PhpCommitLint\Tests\Message;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use GarettRobson\PhpCommitLint\Message\Message;
use GarettRobson\PhpCommitLint\Message\MessagePropertyNotFoundException;

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

}
