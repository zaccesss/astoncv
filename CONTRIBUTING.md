# Contributing

This is a solo university portfolio project, not a collaborative one. Bug reports are welcome, feature requests less so since the site reflects a specific coursework brief.

## What belongs here

- A genuine bug (something broken, a security flaw, incorrect behaviour)
- A typo or documentation fix
- A dependency or CI fix

## What does not belong here

- New feature requests unrelated to fixing something broken
- Design or styling opinions
- Requests to reuse or fork this code beyond what the [licence](LICENSE) already permits

## Reporting a bug

Open an issue with:

- Which page or feature
- What you expected versus what happened
- Steps to reproduce, if not obvious
- PHP version and browser, if relevant

## Pull requests

1. Fork the repository and create a branch named `fix/<short-description>`.
2. Make your changes. Run `php -l` on any changed `.php` files before opening the PR.
3. Follow the existing style: `htmlspecialchars()` on all output, PDO prepared statements for all queries, CSRF token validation on any new form.
4. Open a pull request with a clear title and a description of what changed and why.

## The shared guide

> [!NOTE]
> I keep one shared contributing guide for all my projects, covering software, hardware, writing and everything in between: [zaccesss/contribute](https://github.com/zaccesss/contribute) or on [my site](https://isaacadjei.me/contribute). This file takes precedence where the two differ.
