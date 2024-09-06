# php-commit-lint

php-commit-lint is a PHP tool for linting your git commit messages, ensuring they follow consistent conventions and best practices.

## Table of Contents
- [Installation](#installation)
  - [Install globally](#install-globally)
  - [Install as a dependency](#install-as-a-dependency)
- [Customization](#customization)
  - [Using the wizard](#using-the-wizard)
  - [Override file syntax](#override-file-syntax)
  - [Rule Definitions](#rule-definitions)

## Installation

### Install globally

The following process is how I came to have this setup on my local Ubuntu machine, where I am the only user. By virtue of being the most robustly field-tested it is the recommended process. Some specifics, such as paths, may vary depending on your own environment. I have no experience, nor know of anyone using this in, a Windows environment; so good luck and let me know.

This process instantiates a clone of the repo in a system area and makes it available to all user on the system (depending on OS/FS settings).

1. First we need to create a new directory for the repository, and ensure our user can access it;

    ```sh
    cd /usr/local/share
    sudo mkdir php-commit-lint
    sudo chown <username>:<usergroup> php-commit-lint -R
    ```
    **Note:** The `<username>` and `<usergroup>` values should be replaces with suitable values for your system.

2. Next, clone a fresh copy of the repository to your system and download the composer dependencies;

    ```sh
    git clone git@github.com:garettrobson/PhpCommitLint.git
    cd php-commit-lint
    composer update
    ```

3. Now we need to make symlinks to the executable scripts in the repository;

   ```sh
   cd /usr/local/bin
   sudo ln -s \
       ../share/php-commit-lint/php-commit-lint \
       php-commit-lint
   sudo ln -s \
       ../share/php-commit-lint/php-commit-lint-commit-msg \
       php-commit-lint-commit-msg
   ```

4. Now we can setup a repository to actually use this linter;

    ```sh
    cd /path/to/project/.git/hooks
    ln -s \
        /usr/local/bin/php-commit-lint-commit-msg \
        commit-msg
    ```

5. Auto completion can be enable by following the steps of the `configuration` command.

    ```sh
    php-commit-lint completion --help
    ```

### Install as a dependency

1. Require the `garettrobson/php-commit-lint` package as a `--dev` dependency;

    ```sh
    composer req --dev garettrobson/php-commit-lint
    ```

2. Symlink the `php-commit-lint-commit-msg` executable to the `.git/hooks/commit-msg` file in your repo;

    ```sh
    ln -s \
        ../../vendor/bin/php-commit-lint-commit-msg \
        .git/hooks/commit-msg
    ```

## Customization

The behaviour of the linter can be customized to meet specific needs through local overrides. These are files with the name `.php-commit-lint.json` which instruct the linter on, amongst other things, which rule sets to apply. When run the linter will attempt to load 2 override files;
* The current users home directory - i.e `~/.php-commit-lint.json`.
* The closest override from the current working directory - Starting from the current working directory and recursing parent directories (similar to how the `git` command finds the current repository)

Ideally you would have common rule sets defined in the home directory local override file, with specific rule sets being used on a project-by-project basis.

### Using the wizard

The `php-commit-lint` command comes with tool which will walk you though creating a local override file quickly. When inside a project directory run the `init` command.

```sh
php-commit-lint init
```

The wizard will ask you a number of questions to determine the location to create the local override file and which rule sets to use. These choices can also be made by passing additional information as arguments and options.

The following will setup a new local overrides file in the current directory using the `formatting50-72` and `conventional-commits-basic` rule sets;

```sh
php-commit-lint init . \
    -r formatting50-72 \
    -r conventional-commits-basic \
    -y
```

**`Example: /path/to/project/.php-commit-lint.json`**
```json
{
    "using": [
        "formatting50-72",
        "conventional-commits-basic"
    ]
}
```

### Override file syntax

Override files shares the same syntax as the other JSON-based configuration files used by php-commit-lint (see the JSON files in the `res/` directory). The file must contain a JSON representation of the following data as described bellow

* `configurationContainer` - An _object_ that may contain any of the following keys and associated data.
    * `includes` - An **array of string paths** containing additional JSON files to import (Can be relative or absolute). These are immediately imported at the time they are discovered.
    * `using` - An **array of string rule set names** contains the rule sets to use when linting. *NOTE*: When a `using` value is found it overrides any previous `using` values; effectively you only get the last `using` you included.
    * `patches` - An **array of JSON Patch functions** to apply to the compiled rules. These are **appended** across all included files.
    * `types` - An **object** that maps friendly and descriptive names to rule classes. The file `res/rules/types.json` contains the default set of rules.
    * `ruleSets` - An **object** that maps rule set names to a rule set definition.
        * `ruleSetDefinition` - An **object** that maps rule names to a rule definition.
            * `ruleDefinition` - An **object** that describes a rule. See [Rule Definitions
](#rule-definitions)

The following contrived example demonstrates what a `.php-commit-lint.json` that has all of the above used in someway;

**`Example: /home/someone/.php-commit-lint.json`**
```json
{
    "include": [
        "/path/an/absolute.json",
        "../path/to/relative.json",
    ],
    "types": {
        "example-type": "\\Vendor\\Package\\ExampleRule",
    },
    "ruleSets" : {
        "my-rule-set": {
            "my-rule": {
                "type": "example-type",
                "aParameter": "An example value"
            }
        }
    },
    "using": [
        "my-rule-set"
    ],
    "patches": {
        {
            "op": "add",
            "path": "/my-rule/aParameter",
            "value": "An example overridden value"
        }
    }
}
```

### Rule Definitions

A php-commit-lint rule definition, in JSON, can consist of as little as a `type` which is a _string_ FQCN or a named `type` that resolved to a FQCN. Depending on the class being used there may be additional properties which can be set to further configure the rule. Examples of these exist in the `res/rules/` directory of the project.

There are a small number of reserved keys when it comes to a rule definitions, these are generated at runtime for tracking and debugging;
* `name` - The name of the rule that defined this definition
* `from` - The name of the rule set that defined this definition
* `class` - The FQCN resolved from `types`
* `included` - Path of the file that included this definition
