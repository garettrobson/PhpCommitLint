<?php

declare(strict_types=1);

namespace GarettRobson\PhpCommitLint\Validation;

use stdClass;
use RuntimeException;
use Swaggest\JsonDiff\JsonPatch;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Filesystem\Filesystem;

class ValidatorConfiguration
{
    protected Filesystem $filesystem;
    protected stdClass $types;
    protected stdClass $ruleSets;

    /** @var array<stdClass> $patches */
    protected array $patches = [];

    /** @var array<string> $using */
    protected array $using = [];

    public function __construct()
    {
        $this->filesystem = new Filesystem();
        $this->types = new stdClass();
        $this->ruleSets = new stdClass();
    }

    public function includeFile(string $path): self
    {
        $path = Path::canonicalize($path);

        $json = $this->filesystem->readfile($path);
        $descriptor = json_decode($json);

        if(!is_object($descriptor)) {
            throw new RuntimeException(sprintf(
                'Expected object, received %s from decoding %s',
                gettype($descriptor),
                $path,
            ));
        }

        if ($includes = $descriptor->includes ?? false) {
            foreach ($includes as $includePath) {
                $includePath = Path::canonicalize($includePath);
                $includePath = $this->filesystem->isAbsolutePath($includePath) ?
                    $includePath :
                    Path::makeAbsolute($includePath, dirname($path))
                ;
                $this->includeFile($includePath);
            }
        }

        if($types = $descriptor->types ?? false) {
            $this->types = (object)array_merge(
                (array)$this->types,
                (array)$types,
            );
        }

        if ($ruleSets = $descriptor->ruleSets ?? false) {
            $this->ruleSets = (object)array_merge(
                (array)$this->ruleSets,
                (array)$ruleSets,
            );
        }

        if ($patches = $descriptor->patches ?? false) {
            array_push($this->patches, ...$patches);
        }

        if ($using = $descriptor->using ?? false) {
            $this->using = $using;
        }

        return $this;
    }

    /**
     * @return array<Rule>
     */
    public function getRules(): array
    {
        $rules = new stdClass();
        foreach($this->using as $ruleSetName) {
            $rules = (object)array_merge(
                (array)$rules,
                (array)$this->getRuleSet($ruleSetName)
            );
        }

        $patch = JsonPatch::import($this->patches);
        $patch->apply($rules, true);

        foreach($rules as &$rule) {
            $class = $rule->type;
            $parameters = $rule->parameters ?? [];

            if(!is_string($class)) {
                throw new RuntimeException(sprintf(
                    'Expected class type of string, received %s',
                    gettype($class),
                ));
            }
            if(!class_exists($class, true)) {
                throw new RuntimeException(sprintf(
                    'Class %s does not exist',
                    $class,
                ));
            } elseif (!is_subclass_of($class, Rule::class, true)) {
                throw new RuntimeException(sprintf(
                    'Expected %s to be subclass of %s, parents are %s',
                    $class,
                    Rule::class,
                    implode(', ', class_parents($class)),
                ));
            }

            $rule = new $class(...$parameters);
        }

        return (array)$rules;
    }

    protected function getRuleSet(string $ruleSetName): stdClass
    {
        $ruleSet = $this->ruleSets->$ruleSetName ?? [];
        foreach($ruleSet as &$rule) {
            $rule->type = $this->getType($rule->type);
        }
        return $ruleSet;
    }

    protected function getType(string $typeName): string
    {
        return $this->types->$typeName ?? $typeName;
    }
}
