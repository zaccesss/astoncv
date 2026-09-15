# Workflows

| Workflow | Runs on | What it does |
| --- | --- | --- |
| [`php-lint.yml`](php-lint.yml) | Push to `main`, every pull request | Runs `php -l` syntax checks on every PHP file outside `vendor/` |
| [`markdownlint.yml`](markdownlint.yml) | Push to `main`, every pull request | Lints every markdown file against [`.markdownlint.json`](../../.markdownlint.json) |

`markdownlint.yml` is runnable manually via `workflow_dispatch` from the Actions tab.
