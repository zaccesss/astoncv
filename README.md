<p align="center">
  <img src="https://capsule-render.vercel.app/api?type=waving&color=gradient&height=120&section=header&text=AstonCV&fontSize=48&fontAlignY=32&fontColor=ffffff&animation=fadeIn" />
</p>

<p align="center">
  <img src="https://readme-typing-svg.demolab.com?font=Fira+Code&size=22&pause=1200&color=5c2d82&center=true&vCenter=true&width=600&height=55&lines=Full-Stack+CV+Database+Website;PHP+%7C+MySQL+%7C+Custom+CSS;Built+at+Aston+University;Secure+%7C+Responsive+%7C+Deployed" />
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Aston_University-Project-5c2d82?style=for-the-badge&logo=academia" />
  <img src="https://img.shields.io/badge/PHP-8.2-777BB4?style=for-the-badge&logo=php&logoColor=white" />
  <img src="https://img.shields.io/badge/MySQL-Database-4479A1?style=for-the-badge&logo=mysql&logoColor=white" />
  <img src="https://img.shields.io/badge/mPDF-PDF_Export-red?style=for-the-badge" />
  <img src="https://img.shields.io/badge/Status-Live-brightgreen?style=for-the-badge" />
</p>

---

## Links

<p align="center">
  <a href="http://astoncv.zacess.com">
    <img src="https://img.shields.io/badge/Custom_Link-astoncv.zacess.com-5c2d82?style=for-the-badge&logo=google-chrome&logoColor=white">
  </a>
  <a href="http://240191278.cs2410-web01pvm.aston.ac.uk">
    <img src="https://img.shields.io/badge/Live_Site-Aston_Server-0066CC?style=for-the-badge&logo=google-chrome&logoColor=white">
  </a>
  <a href="https://www.linkedin.com/in/isaacadjei">
    <img src="https://img.shields.io/badge/LinkedIn-Isaac_Adjei-0a66c2?style=for-the-badge&logo=linkedin&logoColor=white">
  </a>
  <a href="mailto:contact@isaacadjei.me">
    <img src="https://img.shields.io/badge/Email-contact@isaacadjei.me-ff6f61?style=for-the-badge&logo=gmail&logoColor=white">
  </a>
</p>

---

## Quick Navigation

<p align="center">
  <a href="#overview">Overview</a> •
  <a href="#live-site">Live Site</a> •
  <a href="#features">Features</a> •
  <a href="#security">Security</a> •
  <a href="#tech-stack">Tech Stack</a> •
  <a href="#languages-and-tools">Languages & Tools</a> •
  <a href="#file-structure">File Structure</a> •
  <a href="#setup">Setup</a> •
  <a href="#about">About</a>
</p>

---

<a id="overview"></a>

## Overview

AstonCV is a full-stack CV database website built as a **Portfolio** at Aston University. It allows anyone to browse and search student CVs publicly, register an account, manage their own CV once logged in, and download any CV as a professionally formatted PDF.

Built with plain PHP 8.2 and MySQL - no frameworks - and deployed on the Aston University internal hosting server. PDF generation is handled server-side using the mPDF library installed via Composer. A custom domain redirect is configured via Cloudflare so the site is accessible at both the Aston URL and the short link.

The UI uses Aston University purple throughout, with Space Grotesk and DM Sans fonts loaded from Google Fonts, real campus photography across all pages, and animations including scroll reveal on cards, an animated stats counter bar, a CSS marquee strip, and a preloader.

---

<a id="live-site"></a>

## Live Site

- Custom link: **http://astoncv.zacess.com** (redirects via Cloudflare)
- Full Aston URL: **http://240191278.cs2410-web01pvm.aston.ac.uk**

Both links point to the same live site.

---

<a id="features"></a>

## Features

### Public

- Browse all CVs as cards in a responsive grid with initials avatars
- Search CVs by name or key programming language
- Filter CVs live by programming language - no page reload
- Sort CVs - A to Z, Z to A, most viewed - no page reload
- View full CV details styled as a real CV document with sidebar
- Download any CV as a PDF (mPDF server-side generation)
- Register a new account with password strength checker
- Contact form with enquiry submission

### Registered Users

- Login with CSRF protection and brute force lockout
- Remember Me (30-day cookie)
- Personal dashboard with CV preview, completeness score and view stats
- Update CV details, profile picture and password
- Profile picture upload (JPG, PNG, GIF, WEBP - max 2MB)
- Owner-only edit button on CV detail page

### UI and Design

- Aston University purple (#5c2d82) throughout
- Space Grotesk headings, DM Sans body text via Google Fonts CDN
- Campus photography on every page - hero, login, register, update, dashboard
- Full-width campus hero with purple gradient overlay on index
- Animated stats bar with counting numbers
- CSS marquee strip below hero
- Scroll reveal animations on CV cards using IntersectionObserver
- Preloader on first page load
- Sticky dark navbar with scroll blur effect
- Real footer with navigation and project links
- Fully responsive and mobile friendly

---

<a id="security"></a>

## Security Measures

| #   | Measure                  | Implementation                                               |
| --- | ------------------------ | ------------------------------------------------------------ |
| 1   | XSS Prevention           | `htmlspecialchars()` on all output                           |
| 2   | SQL Injection Prevention | PDO prepared statements on all queries                       |
| 3   | Password Hashing         | bcrypt via `password_hash()`                                 |
| 4   | Password Verification    | `password_verify()` on login                                 |
| 5   | Session Authentication   | `$_SESSION['user_id']` checked on all protected pages        |
| 6   | Authorisation            | Users can only edit their own CV - owner check enforced      |
| 7   | Server-side Validation   | All form fields validated in PHP before database write       |
| 8   | CSRF Protection          | Hidden token validated on every POST form submission         |
| 9   | Brute-force Protection   | Account locked for 15 min after 5 failed login attempts      |
| 10  | File Upload Validation   | Type whitelist and 2MB size limit on profile picture uploads |
| 11  | Honeypot                 | Hidden field on contact form silently blocks spam bots       |

---

<a id="tech-stack"></a>

## Tech Stack

| Layer           | Technology                                 |
| --------------- | ------------------------------------------ |
| Backend         | PHP 8.2 - no framework                     |
| Database        | MySQL                                      |
| Frontend        | HTML5, CSS3, JavaScript                    |
| PDF Generation  | mPDF v8.2 via Composer                     |
| Fonts           | Space Grotesk and DM Sans via Google Fonts |
| Local Dev       | XAMPP (Apache + MySQL)                     |
| Deployment      | Aston University internal server (Apache)  |
| Custom Domain   | Cloudflare CNAME and Page Rule redirect    |
| Version Control | Git and GitHub                             |

---

<a id="languages-and-tools"></a>

## Languages & Tools Used

<div align="center">

| <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/php/php-original.svg" width="60"/> | <img src="https://techstack-generator.vercel.app/mysql-icon.svg" width="60"/> | <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/html5/html5-original.svg" width="60"/> | <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/css3/css3-original.svg" width="60"/> | <img src="https://techstack-generator.vercel.app/js-icon.svg" width="60"/> |
| :---------------------------------------------------------------------------------------------: | :---------------------------------------------------------------------------: | :-------------------------------------------------------------------------------------------------: | :-----------------------------------------------------------------------------------------------: | :------------------------------------------------------------------------: |
|                                           **PHP 8.2**                                           |                                   **MySQL**                                   |                                              **HTML5**                                              |                                             **CSS3**                                              |                               **JavaScript**                               |

| <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/git/git-original.svg" width="60"/> | <img src="https://techstack-generator.vercel.app/github-icon.svg" width="60"/> | <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/composer/composer-original.svg" width="60"/> | <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/apache/apache-original.svg" width="60"/> | <img src="https://img.shields.io/badge/XAMPP-Local_Dev-FB7A24?style=for-the-badge&logo=xampp&logoColor=white" /> |
| :---------------------------------------------------------------------------------------------: | :----------------------------------------------------------------------------: | :-------------------------------------------------------------------------------------------------------: | :---------------------------------------------------------------------------------------------------: | :--------------------------------------------------------------------------------------------------------------: |
|                                             **Git**                                             |                                   **GitHub**                                   |                                               **Composer**                                                |                                              **Apache**                                               |                                                    **XAMPP**                                                     |

</div>

---

<a id="file-structure"></a>

## File Structure

| File                  | Purpose                                                 |
| --------------------- | ------------------------------------------------------- |
| `index.php`           | Homepage - browse, search, filter and sort all CVs      |
| `cv.php`              | Full CV detail page with download PDF and print buttons |
| `register.php`        | New user registration with password strength checker    |
| `login.php`           | Login with CSRF protection and brute force lockout      |
| `update.php`          | Update CV details, profile picture and password         |
| `dashboard.php`       | Personal dashboard shown after login                    |
| `logout.php`          | Destroys session and redirects to homepage              |
| `export_cv.php`       | Generates and downloads a CV as PDF using mPDF          |
| `contact_handler.php` | Processes contact form with honeypot bot protection     |
| `db.php`              | Shared PDO database connection with try/catch           |
| `config.php`          | Database credentials - gitignored, never in repo        |
| `config.example.php`  | Placeholder credentials safe for GitHub                 |
| `style.css`           | Full custom stylesheet - Space Grotesk, Aston purple    |
| `images/`             | Campus photography used across all pages                |
| `uploads/`            | User profile picture storage - gitignored               |
| `composer.json`       | mPDF dependency declaration                             |
| `vendor/`             | mPDF library - gitignored, install via Composer         |

---

<a id="setup"></a>

## Local Setup

1. Clone the repo into `C:\xampp\htdocs\astoncv`
2. Copy `config.example.php` to `config.php` and fill in your local database credentials
3. Create the MySQL database and set up the schema (contact the maintainer for the schema file)
4. Run `composer install` to install mPDF
5. Start Apache and MySQL in XAMPP
6. Visit `http://localhost/astoncv`

> `config.php` and `vendor/` are gitignored. Your real credentials and the mPDF library are never stored in this repo.

---

<a id="about"></a>

## About

**Isaac Adjei**
BEng Electronic Engineering and Computer Science - [Aston University](https://www.aston.ac.uk)
[isaacadjei.me](https://isaacadjei.me)

---

<a id="contact"></a>

## Contact and Support

Open an [issue](https://github.com/zaccesss/astoncv/issues) in this repository for questions or bugs.

You can also reach me directly at [contact@isaacadjei.me](mailto:contact@isaacadjei.me) or via my [website contact page](https://isaacadjei.me/contact).

---

<p align="center">
  <img src="https://capsule-render.vercel.app/api?type=waving&color=gradient&height=80&section=footer" />
</p>
