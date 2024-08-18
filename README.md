# php-commit-lint

A PHP tool for linting your commit messages.

## Installation

First you will need to require the package, we recommend you do this as a `--dev` dependency.

```sh
composer req --dev garettrobson/php-commit-lint
```

Next you need to bind the `php-commit-lint` executable to the `.git/hooks/commit-msg` file in your repo

```sh
ln -s ../../vendor/bin/php-commit-lint .git/hooks/commit-msg
```
