# php-commit-lint

A PHP tool for linting your commit messages.

## Installation

### As a package

_The recommended way to use this in development._

First change directory to the composer project you want to use `php-commit-lint` on;

```sh
cd /my/project/path
```

Require the `garettrobson/php-commit-lint` package as a `--dev` dependency;

```sh
composer req --dev garettrobson/php-commit-lint
```

Symlink the `php-commit-lint-commit-msg` executable to the `.git/hooks/commit-msg` file in your repo;

```sh
ln -s ../../vendor/bin/php-commit-lint-commit-msg .git/hooks/commit-msg
```

Test it works by trying to commit the change with an invalid commit message;

```sh
git add composer.json
git commit -m 'try(bad scope): a description that is too long, and will fail because of numerous problems'
```

You should see output that looks similar to the following;

```

PHP Commit Lint
===============

The following errors occurred:
- Title exceeds 50 characters
- Type of try not allowed, must be one of: fix, feat, build, chore, ci, docs, style, refactor, perf, test
- Scope "bad scope" does not conform to expected pattern: /^[\w-]+$/
- Description "a description that is too long, and will fail because of numerous problems" does not conform to expected pattern: /^[A-Z0-9].*$/


 [ERROR] Commit message failed linting

```

### Global install

When installing `php-commit-lint` as an executable which can run from anywhere on a system, it is recommended to continue to maintain the installation with git.

First we need to create a new directory for the repository, and ensure our user can access it;

```
cd /usr/local/share
sudo mkdir php-commit-lint
sudo chown <username>:<usergroup> php-commit-lint -R
```
**Note:** The `<username>` and `<usergroup>` values should be replaces with suitable values for your system.

Next, clone a fresh copy of the repository to your system;

```
git clone git@github.com:garettrobson/PhpCommitLint.git
```

Now we need to make symlinks to the executable scripts in the repository;

```
cd /usr/local/bin
sudo ln -s ../share/php-commit-lint/php-commit-lint php-commit-lint
sudo ln -s ../share/php-commit-lint/php-commit-lint-commit-msg php-commit-lint-commit-msg
```
**Note:** At this point php-commit-lint is installed, but no repositories will _automatically_ use this linter until they are individually configured.

To test the setup we can now try to validate an arbitrary commit message;

```
echo 'try(bad scope): a description that is too long, and will fail because of numerous problems' | php-commit-lint-commit-msg
```

This should produce output similar to;

```

PHP Commit Lint: Message Lint
=============================

Messages
--------

- Line 1 is 90 characters long, exceeds 50 limit
- Unexpected type of value try, must be one of; ["fix","feat","build","chore","ci","docs","style","refactor","perf","test"]


 [ERROR] Commit message failed linting

```

Now we can setup a repository to actually use this linter;
```
cd /path/to/project/.git/hooks
ln -s /usr/local/bin/php-commit-lint-commit-msg commit-msg
```

## Customization

When run `php-commit-lint` will load search for the closest `.php-commit-lint.json` file, first checking the current working directory then recursing parent directories, similar to how `git` looks for a `.git` directory. The `.php-commit-lint.json` shares the same syntax and structure as the other JSON-based configuration files used by `php-commit-lint`.

The json file should contain an object definition, not an array or any other valid JSON type data. This root node may contain any properties, however `php-commit-lint` is only interested in a small number of them. When a JSON file is included into the configuration system it processes the following keys, when present, in the following order.

* `includes` - An array of paths (can be relative paths) to additional JSON files to import. These are immediately imported as they are discovered.
* `using` - An array of string; each string naming a rule set to be used while linting. *NOTE*: When a `using` value is found it overrides any previous `using` values; effectively you only get the last `using` you included.
* `patches` - An array containing objects conforming to the PHP Patch notation. These are **collected** across all included files.
* `types` - An object that maps friendly and descriptive names to rule classes. The file `res/rules/types.json` contains the default set.
* `ruleSets` - Rule sets are the most complex of the data types. The `ruleSets` property should map to an object, that object's properties map named rule set to an object defining that rule set. The rule set object maps a friendly rule name to a rule object. The rule object has only one required property `type`, which can either be a reference to a key in `types` (see above), or a FQCN. Optionally, you can provide the property `parameters` which will be passed to the constructor of the related `type`.

The following example;

***Note**: The property "comment" is used bellow to bring attention to important information about the objects it is associated with*

```
{
    "ruleSets": {
        "my-rule-set": {
            "my-rule": {
                "type": "property-regex",
                "note": "The first parameter is \"incorrect\" and should be \"type\", this is address with the first patch bellow"
                "parameters":[
                    "incorrect",
                    "/^[^A-Z]*$/",
                    "The type must be lowercase"
                ]
            },
            "line-length": {
                "note": "This is invalid, but will be overridden because the `using` property includes this named rule before basic, which redefines the line-length rule"
            }
        }
    },
    "using": [
        "my-rule-set",
        "basic"
    ],
    "patches": {
        {
            "op": "add",
            "path": "/my-rule/parameters.0",
            "value": ""
        },
        {
            "op": "replace",
            "path": "/line-length/parameters",
            "value": [
                [10, 0],
                10
            ]
        }
    }
}
```

### Collecting Rules and Apply Patches
