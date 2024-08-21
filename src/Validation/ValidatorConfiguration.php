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

    public function includeFile(string $path, array &$included = []): array
    {
        $path = Path::canonicalize($path);

        $json = $this->filesystem->readfile($path);
        $descriptor = json_decode($json);
        $included[] = $path;

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
                    Path::makeAbsolute($includePath, dirname(realpath($path)))
                ;
                $this->includeFile($includePath, $included);
            }
        }

        if ($using = $descriptor->using ?? false) {
            $this->using = $using;
        }

        if ($patches = $descriptor->patches ?? false) {
            array_push($this->patches, ...$patches);
        }

        if($types = $descriptor->types ?? false) {
            $this->types = (object)array_merge(
                (array)$this->types,
                (array)$types,
            );
        }

        if ($ruleSets = $descriptor->ruleSets ?? false) {
            foreach ($ruleSets as $ruleSetName => &$ruleSet) {
                foreach ($ruleSet as $ruleName => &$rule) {
                    $rule->included = $path;
                    $rule->name = $ruleName;
                    $rule->from = $ruleSetName;
                }
            }
            $this->ruleSets = (object)array_merge(
                (array)$this->ruleSets,
                (array)$ruleSets,
            );
        }

        return $included;
    }

    /**
     * @return array<stdClass>
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

        return (array)$rules;
    }

    protected function getRuleSet(string $ruleSetName): stdClass
    {
        $ruleSet = $this->ruleSets->$ruleSetName ?? [];
        foreach($ruleSet as &$rule) {
            $rule->class = $this->getType($rule->type);
        }
        return $ruleSet;
    }

    protected function getType(string $typeName): string
    {
        return $this->types->$typeName ?? $typeName;
    }
}
