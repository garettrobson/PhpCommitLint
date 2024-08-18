# php-commit-lint

A PHP tool for linting your commit messages.

## Installation

### As a package

_The recommended way to use this in development.s_

First change directory to the composer project you want to use `php-commit-link` on.

```sh
cd /my/project/path
```

Require the `garettrobson/php-commit-lint` package as a `--dev` dependency;

```sh
composer req --dev garettrobson/php-commit-lint
```

Symlink the `php-commit-lint` executable to the `.git/hooks/commit-msg` file in your repo;

```sh
ln -s ../../vendor/bin/php-commit-lint .git/hooks/commit-msg
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
