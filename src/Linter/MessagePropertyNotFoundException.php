<?php

declare(strict_types=1);

namespace GarettRobson\PhpCommitLint\Linter;

use Exception;
use Psr\Container\NotFoundExceptionInterface;

class MessagePropertyNotFoundException extends Exception implements NotFoundExceptionInterface
{
}
