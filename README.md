# AstonCV

[![PHP Lint](https://github.com/zaccesss/astoncv/actions/workflows/php-lint.yml/badge.svg)](https://github.com/zaccesss/astoncv/actions/workflows/php-lint.yml)
[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)
[![Version](https://img.shields.io/badge/version-2.2.3-blue.svg)](CHANGELOG.md)
[![Status](https://img.shields.io/badge/status-live-brightgreen.svg)](#live-site)

AstonCV is a full-stack CV database website, built as a university portfolio project in plain PHP and MySQL. Anyone can browse and search student CVs publicly, register an account, manage their own CV once logged in and download any CV as a professionally formatted PDF.

## Quick Navigation

<p align="center">
  <a href="#live-site">Live Site</a> •
  <a href="#documentation-hub">Documentation</a> •
  <a href="#tech-stack">Tech Stack</a> •
  <a href="#repository-structure">Structure</a> •
  <a href="#contact-and-support">Support</a>
</p>

---

<a id="live-site"></a>

## Live Site

The site is live at [astoncv.zacess.com](http://astoncv.zacess.com).

---

<a id="documentation-hub"></a>

## Documentation Hub

<p align="center">
  <a href="DOCUMENTATION.md">Full Documentation</a> &nbsp;•&nbsp;
  <a href="SECURITY.md">Security Policy</a> &nbsp;•&nbsp;
  <a href="CONTRIBUTING.md">Contributing</a> &nbsp;•&nbsp;
  <a href="CHANGELOG.md">Changelog</a> &nbsp;•&nbsp;
  <a href="ROADMAP.md">Roadmap</a>
</p>

See [DOCUMENTATION.md](DOCUMENTATION.md) for the full feature list, security implementation detail and local setup instructions.

---

<a id="tech-stack"></a>

## Tech Stack

<div align="center">

| <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/php/php-original.svg" width="60"/> | <img src="https://techstack-generator.vercel.app/mysql-icon.svg" width="60"/> | <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/html5/html5-original.svg" width="60"/> | <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/css3/css3-original.svg" width="60"/> | <img src="https://techstack-generator.vercel.app/js-icon.svg" width="60"/> |
|:---:|:---:|:---:|:---:|:---:|
| **PHP 8.2** | **MySQL** | **HTML5** | **CSS3** | **JavaScript** |

| <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/git/git-original.svg" width="60"/> | <img src="https://techstack-generator.vercel.app/github-icon.svg" width="60"/> | <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/composer/composer-original.svg" width="60"/> | <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/apache/apache-original.svg" width="60"/> | <img src="https://cdn.simpleicons.org/xampp" width="60"/> |
|:---:|:---:|:---:|:---:|:---:|
| **Git** | **GitHub** | **Composer** | **Apache** | **XAMPP** |

</div>

---

<a id="repository-structure"></a>

## Repository Structure

```
astoncv/
├── public/                  Web root, everything the server serves directly
│   ├── index.php            Homepage: browse, search, filter and sort all CVs
│   ├── cv.php                Full CV detail page with download PDF and print buttons
│   ├── register.php          New user registration
│   ├── login.php              Login with CSRF protection and brute force lockout
│   ├── update.php             Update CV details, profile picture and password
│   ├── dashboard.php          Personal dashboard shown after login
│   ├── logout.php             Destroys the session and redirects to the homepage
│   ├── export_cv.php          Generates and downloads a CV as a PDF using mPDF
│   ├── contact_handler.php    Processes the contact form
│   ├── db.php                 Shared PDO database connection
│   ├── config.php             Database credentials, gitignored
│   ├── config.example.php     Placeholder credentials safe for GitHub
│   ├── style.css              Full custom stylesheet
│   ├── images/                Campus photography used across all pages
│   └── uploads/                User profile picture storage, gitignored
├── vendor/                   mPDF library, gitignored, installed via Composer
├── composer.json              mPDF dependency declaration
├── DOCUMENTATION.md            Full feature list and security detail
└── CHANGELOG.md                What changed and when
```

---

<a id="contact-and-support"></a>

## Contact and Support

> [!TIP]
> This project is maintained by Isaac Adjei. Questions and bug reports can be raised as an [issue](https://github.com/zaccesss/astoncv/issues) in this repository. Reach me directly at [contact@isaacadjei.me](mailto:contact@isaacadjei.me) or through [isaacadjei.me/contact](https://isaacadjei.me/contact).

> [!IMPORTANT]
> Found a security issue? Do not open a public issue, see [SECURITY.md](SECURITY.md) for how to report it privately.

See [NOTICE.md](NOTICE.md) for licensing details on the campus photography and [LICENSE](LICENSE) for the MIT licence covering the project code.
