<?php

declare(strict_types=1);

namespace GarettRobson\PhpCommitLint\Message;

use Psr\Container\NotFoundExceptionInterface;

class MessagePropertyNotFoundException extends \Exception implements NotFoundExceptionInterface {}
