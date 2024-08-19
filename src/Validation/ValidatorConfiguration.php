<?php

declare(strict_types=1);

namespace GarettRobson\PhpCommitLint\Validation;

use RuntimeException;
use Swaggest\JsonDiff\JsonPatch;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Filesystem\Filesystem;

class ValidatorConfiguration
{
    protected Filesystem $filesystem;
    protected array $types = [];
    protected array $ruleSets = [];
    protected array $patches = [];
    protected array $using = [];

    public function __construct()
    {
        $this->filesystem = new Filesystem();
    }

    public function includeFile(string $path)
    {
        $path = Path::canonicalize($path);

        $json = $this->filesystem->readfile($path);
        $descriptor = json_decode($json, true);

        if ($includes = $descriptor['includes'] ?? false) {
            foreach ($includes as $includePath) {
                $includePath = Path::canonicalize($includePath);
                $includePath = $this->filesystem->isAbsolutePath($includePath) ?
                    $includePath :
                    Path::makeAbsolute($includePath, dirname($path))
                ;
                $this->includeFile($includePath);
            }
        }

        if($types = $descriptor['types'] ?? false) {
            $this->types = array_merge(
                $this->types,
                $types,
            );
        }

        if ($ruleSets = $descriptor['ruleSets'] ?? false) {
            $this->ruleSets = array_merge(
                $this->ruleSets,
                $ruleSets,
            );
        }

        if ($patches = $descriptor['patches'] ?? false) {
            array_push($this->patches, ...$patches);
        }

        if ($using = $descriptor['using'] ?? false) {
            $this->using = $using;
        }
    }

    /**
     * @return array<Rule>
     */
    public function getRules(): array
    {
        $rules = [];
        foreach($this->using as $ruleSetName) {
            $rules = array_merge(
                $rules,
                $this->getRuleSet($ruleSetName)
            );
        }

        JsonPatch::import($this->patches)->apply($rules);

        foreach($rules as &$rule) {
            $class = $rule['type'];
            $parameters = $rule['parameters'] ?? [];

            if (!is_subclass_of($class, Rule::class, true)) {
                throw new RuntimeException(sprintf(
                    'Expected %s to be subclass of %s, parents are %s',
                    $class,
                    Rule::class,
                    implode(', ', class_parents($class)),
                ));
            }

            $rule = new $class(...$parameters);
        }

        return $rules;
    }

    protected function getRuleSet(string $ruleSetName): array
    {
        $ruleSet = $this->ruleSets[$ruleSetName];
        foreach($ruleSet as &$rule) {
            $rule['type'] = $this->getType($rule['type']);
        }
        return $ruleSet;
    }

    protected function getType(string $typeName): string
    {
        return $this->types[$typeName] ?? $typeName;
    }
}
