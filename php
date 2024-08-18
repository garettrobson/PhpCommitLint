feat(user-auth): add multi-factor authentication

This commit introduces multi-factor authentication for user accounts.
The new feature allows users to enable 2FA using either SMS or
authenticator apps.

Key changes:
- Added new database table for storing 2FA settings
- Implemented SMS verification flow
- Added support for TOTP-based authenticator apps
- Updated user settings UI to include 2FA options

Breaking changes:
The user login API now returns a new field \'requires_2fa\'
which must be handled by clients.

Closes: #123
Reviewed-by: Alice
BREAKING CHANGE: User login API response structure changed
<?php

include 'vendor/autoload.php';

use GarettRobson\PhpCommitLint\Application\LintApplication;
use GarettRobson\PhpCommitLint\Command\LintMessageCommand;

$application = new LintApplication();

$application->addCommands([
    new LintMessageCommand()
]);

$application->setDefaultCommand('message:lint', true);

$application->run();
