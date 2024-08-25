<?php

declare(strict_types=1);

namespace GarettRobson\PhpCommitLint\Application;

use GarettRobson\PhpCommitLint\Command\ConfigCommand;
use GarettRobson\PhpCommitLint\Command\InitCommand;
use GarettRobson\PhpCommitLint\Command\LintCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PhpCommitLintApplication extends Application
{
    public function __construct()
    {
        $composerJsonPath = __DIR__.'/../../composer.json';
        $json = file_get_contents($composerJsonPath);

        if (false === $json) {
            throw new \RuntimeException(sprintf(
                'Unable to read expected composer.json file %s',
                $composerJsonPath
            ));
        }

        /** @var \stdClass $composerJsonDefinition */
        $composerJsonDefinition = json_decode($json, false, 512, JSON_THROW_ON_ERROR);

        $name = is_string($composerJsonDefinition->name ?? null) ?
            $composerJsonDefinition->name :
            'php-commit-lint';
        $version = is_string($composerJsonDefinition->version ?? null) ?
            $composerJsonDefinition->version :
            'error';

        parent::__construct($name, $version);
        $this->addCommands([
            new LintCommand(),
            new ConfigCommand(),
            new InitCommand(),
        ]);
    }

    protected function configureIO(InputInterface $input, OutputInterface $output): void
    {
        parent::configureIO($input, $output);
        $output->getFormatter()->setStyle('error', new OutputFormatterStyle('red'));
        $output->getFormatter()->setStyle('text', new OutputFormatterStyle('gray'));
    }
}
