# AstonCV

[![PHP Lint](https://github.com/zaccesss/astoncv/actions/workflows/php-lint.yml/badge.svg)](https://github.com/zaccesss/astoncv/actions/workflows/php-lint.yml)
[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)
[![Version](https://img.shields.io/badge/version-2.2.3-blue.svg)](CHANGELOG.md)
[![Status](https://img.shields.io/badge/status-live-brightgreen.svg)](#live-site)

AstonCV is a full-stack CV database website, built as a university portfolio project. Anyone can browse and search student CVs publicly, register an account, manage their own CV once logged in and download any CV as a professionally formatted PDF.

It is written in plain PHP 8.2 and MySQL with no framework. PDF generation runs server-side with the mPDF library, installed via Composer. A custom domain redirect is configured through Cloudflare, so the site is reachable at a short, memorable link rather than the raw hosting URL.

The UI uses Aston University purple throughout, with Space Grotesk and DM Sans fonts from Google Fonts, real campus photography across all pages and animations including scroll reveal on cards, an animated stats counter bar, a CSS marquee strip and a preloader.

## Quick Navigation

<p align="center">
  <a href="#live-site">Live Site</a> •
  <a href="#features">Features</a> •
  <a href="#security">Security</a> •
  <a href="#tech-stack">Tech Stack</a> •
  <a href="#file-structure">Files</a> •
  <a href="#local-setup">Setup</a> •
  <a href="#contact-and-support">Support</a>
</p>

---

<a id="live-site"></a>

## Live Site

The site is live at [astoncv.zacess.com](http://astoncv.zacess.com), which redirects to the university's internal student hosting server via Cloudflare.

---

<a id="features"></a>

## Features

### Public

- Browse all CVs as cards in a responsive grid with initials avatars
- Search CVs by name or key programming language
- Filter CVs live by programming language with no page reload
- Sort CVs A to Z, Z to A or by most viewed with no page reload
- View full CV details styled as a real CV document with a sidebar
- Download any CV as a PDF through server-side mPDF generation
- Register a new account with a password strength checker
- Submit a contact form enquiry

### Registered Users

- Log in with CSRF protection and brute force lockout
- Stay signed in with a 30-day Remember Me cookie
- View a personal dashboard with CV preview, completeness score and view stats
- Update CV details, profile picture and password
- Upload a profile picture (JPG, PNG, GIF or WEBP, up to 2MB)
- See an owner-only edit button on the CV detail page

### UI and Design

- Aston University purple (#5c2d82) throughout
- Space Grotesk headings and DM Sans body text via the Google Fonts CDN
- Campus photography on every page: hero, login, register, update and dashboard
- Full-width campus hero with a purple gradient overlay on the homepage
- Animated stats bar with counting numbers
- CSS marquee strip below the hero
- Scroll reveal animations on CV cards using IntersectionObserver
- Preloader on first page load
- Sticky dark navbar with a scroll blur effect
- Fully responsive and mobile friendly

---

<a id="security"></a>

## Security

Security controls built into the project include:

- XSS prevention with `htmlspecialchars()` on all output
- SQL injection prevention with PDO prepared statements on all queries
- Password hashing with `password_hash()` and verification with `password_verify()`
- Session authentication checked on every protected page
- Authorisation so a user can only edit their own CV
- Server-side validation on all form fields before any database write
- CSRF token validation on every POST form submission
- Brute-force protection: accounts lock for 15 minutes after 5 failed login attempts
- File upload validation with a type whitelist and a 2MB size limit
- A honeypot field on the contact form to block spam bots

> [!NOTE]
> See [SECURITY.md](SECURITY.md) for the full policy and how to report a vulnerability privately.

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

| Layer | Technology |
|---|---|
| Backend | PHP 8.2, no framework |
| Database | MySQL |
| Frontend | HTML5, CSS3, JavaScript |
| PDF generation | mPDF v8.2 via Composer |
| Fonts | Space Grotesk and DM Sans via Google Fonts |
| Local dev | XAMPP (Apache and MySQL) |
| Custom domain | Cloudflare CNAME and page rule redirect |
| CI | GitHub Actions PHP syntax check |

---

<a id="file-structure"></a>

## File Structure

| File | Purpose |
|---|---|
| `index.php` | Homepage: browse, search, filter and sort all CVs |
| `cv.php` | Full CV detail page with download PDF and print buttons |
| `register.php` | New user registration with a password strength checker |
| `login.php` | Login with CSRF protection and brute force lockout |
| `update.php` | Update CV details, profile picture and password |
| `dashboard.php` | Personal dashboard shown after login |
| `logout.php` | Destroys the session and redirects to the homepage |
| `export_cv.php` | Generates and downloads a CV as a PDF using mPDF |
| `contact_handler.php` | Processes the contact form with honeypot bot protection |
| `db.php` | Shared PDO database connection with try/catch |
| `config.php` | Database credentials, gitignored and never in the repo |
| `config.example.php` | Placeholder credentials safe for GitHub |
| `style.css` | Full custom stylesheet: Space Grotesk, Aston purple |
| `images/` | Campus photography used across all pages |
| `uploads/` | User profile picture storage, gitignored |
| `composer.json` | mPDF dependency declaration |
| `vendor/` | mPDF library, gitignored, installed via Composer |

---

<a id="local-setup"></a>

## Local Setup

1. Clone the repo into `C:\xampp\htdocs\astoncv`
2. Copy `config.example.php` to `config.php` and fill in local database credentials
3. Create the MySQL database and set up the schema
4. Run `composer install` to install mPDF
5. Start Apache and MySQL in XAMPP
6. Visit `http://localhost/astoncv`

> [!IMPORTANT]
> The database schema is not in this repo. Contact the maintainer for the schema file before step 3 or setup will not get past creating the database.

`config.php` and `vendor/` are gitignored, so real credentials and the mPDF library never end up in this repo.

---

<a id="contact-and-support"></a>

## Contact and Support

> [!TIP]
> This project is maintained by Isaac Adjei. Questions and bug reports can be raised as an [issue](https://github.com/zaccesss/astoncv/issues) in this repository. Reach me directly at [contact@isaacadjei.me](mailto:contact@isaacadjei.me) or through [isaacadjei.me/contact](https://isaacadjei.me/contact).

> [!IMPORTANT]
> Found a security issue? Do not open a public issue, see [SECURITY.md](SECURITY.md) for how to report it privately.

See [NOTICE.md](NOTICE.md) for licensing details on the campus photography and [LICENSE](LICENSE) for the MIT licence covering the project code. See [CHANGELOG.md](CHANGELOG.md) for what has changed and [ROADMAP.md](ROADMAP.md) for what is planned.
