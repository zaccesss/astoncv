# AstonCV

<p align="center">
  <img src="https://img.shields.io/badge/PHP-8.2-777BB4?logo=php&logoColor=white" alt="PHP 8.2" />
  <img src="https://img.shields.io/badge/MySQL-Database-4479A1?logo=mysql&logoColor=white" alt="MySQL" />
  <img src="https://img.shields.io/badge/mPDF-PDF_Export-red" alt="mPDF" />
  <img src="https://img.shields.io/badge/Status-Live-brightgreen" alt="Status: Live" />
  <img src="https://github.com/zaccesss/astoncv/actions/workflows/php-lint.yml/badge.svg" alt="PHP Lint status" />
  <img src="https://img.shields.io/badge/Licence-MIT-blue" alt="MIT Licence" />
</p>

AstonCV is a full-stack CV database website, built as a portfolio project at Aston University. Anyone can browse and search student CVs publicly, register an account, manage their own CV once logged in and download any CV as a professionally formatted PDF.

It is written in plain PHP 8.2 and MySQL with no framework, and it is deployed on the Aston University internal hosting server. PDF generation runs server-side with the mPDF library, installed via Composer. A custom domain redirect is configured through Cloudflare, so the site is reachable at both the Aston URL and the short link.

The UI uses Aston University purple throughout, with Space Grotesk and DM Sans fonts from Google Fonts, real campus photography across all pages and animations including scroll reveal on cards, an animated stats counter bar, a CSS marquee strip and a preloader.

## Live site

- Custom link: [astoncv.zacess.com](http://astoncv.zacess.com) (redirects via Cloudflare)
- Full Aston URL: [240191278.cs2410-web01pvm.aston.ac.uk](http://240191278.cs2410-web01pvm.aston.ac.uk)

Both links point to the same live site.

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

### Registered users

- Log in with CSRF protection and brute force lockout
- Stay signed in with a 30-day Remember Me cookie
- View a personal dashboard with CV preview, completeness score and view stats
- Update CV details, profile picture and password
- Upload a profile picture (JPG, PNG, GIF or WEBP, up to 2MB)
- See an owner-only edit button on the CV detail page

### UI and design

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

See [SECURITY.md](SECURITY.md) for the full policy and how to report a vulnerability.

## Tech stack

| Layer            | Technology                                 |
| ----------------- | ------------------------------------------- |
| Backend          | PHP 8.2, no framework                      |
| Database         | MySQL                                      |
| Frontend         | HTML5, CSS3, JavaScript                    |
| PDF generation   | mPDF v8.2 via Composer                     |
| Fonts            | Space Grotesk and DM Sans via Google Fonts |
| Local dev        | XAMPP (Apache and MySQL)                   |
| Deployment       | Aston University internal server (Apache)  |
| Custom domain    | Cloudflare CNAME and page rule redirect    |
| CI               | GitHub Actions PHP syntax check             |
| Version control  | Git and GitHub                             |

## Languages and tools

<div align="center">

| <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/php/php-original.svg" width="60"/> | <img src="https://techstack-generator.vercel.app/mysql-icon.svg" width="60"/> | <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/html5/html5-original.svg" width="60"/> | <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/css3/css3-original.svg" width="60"/> | <img src="https://techstack-generator.vercel.app/js-icon.svg" width="60"/> |
| :---------------------------------------------------------------------------------------------: | :---------------------------------------------------------------------------: | :-------------------------------------------------------------------------------------------------: | :-----------------------------------------------------------------------------------------------: | :------------------------------------------------------------------------: |
|                                           **PHP 8.2**                                           |                                   **MySQL**                                   |                                              **HTML5**                                              |                                             **CSS3**                                              |                               **JavaScript**                               |

| <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/git/git-original.svg" width="60"/> | <img src="https://techstack-generator.vercel.app/github-icon.svg" width="60"/> | <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/composer/composer-original.svg" width="60"/> | <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/apache/apache-original.svg" width="60"/> | <img src="https://img.shields.io/badge/XAMPP-Local_Dev-FB7A24?style=for-the-badge&logo=xampp&logoColor=white" /> |
| :---------------------------------------------------------------------------------------------: | :----------------------------------------------------------------------------: | :-------------------------------------------------------------------------------------------------------: | :---------------------------------------------------------------------------------------------------: | :--------------------------------------------------------------------------------------------------------------: |
|                                             **Git**                                             |                                   **GitHub**                                   |                                               **Composer**                                                |                                              **Apache**                                               |                                                    **XAMPP**                                                     |

</div>

## File structure

| File                   | Purpose                                                    |
| ---------------------- | ----------------------------------------------------------- |
| `index.php`            | Homepage: browse, search, filter and sort all CVs          |
| `cv.php`               | Full CV detail page with download PDF and print buttons    |
| `register.php`         | New user registration with a password strength checker     |
| `login.php`            | Login with CSRF protection and brute force lockout          |
| `update.php`           | Update CV details, profile picture and password             |
| `dashboard.php`        | Personal dashboard shown after login                        |
| `logout.php`           | Destroys the session and redirects to the homepage          |
| `export_cv.php`        | Generates and downloads a CV as a PDF using mPDF            |
| `contact_handler.php`  | Processes the contact form with honeypot bot protection     |
| `db.php`               | Shared PDO database connection with try/catch               |
| `config.php`           | Database credentials, gitignored and never in the repo      |
| `config.example.php`   | Placeholder credentials safe for GitHub                     |
| `style.css`            | Full custom stylesheet: Space Grotesk, Aston purple         |
| `images/`              | Campus photography used across all pages                    |
| `uploads/`             | User profile picture storage, gitignored                    |
| `composer.json`        | mPDF dependency declaration                                  |
| `vendor/`              | mPDF library, gitignored, installed via Composer            |

## Local setup

1. Clone the repo into `C:\xampp\htdocs\astoncv`
2. Copy `config.example.php` to `config.php` and fill in local database credentials
3. Create the MySQL database and set up the schema
4. Run `composer install` to install mPDF
5. Start Apache and MySQL in XAMPP
6. Visit `http://localhost/astoncv`

> [!IMPORTANT]
> The database schema is not in this repo. Contact the maintainer for the schema file before step 3, or setup will not get past creating the database.

`config.php` and `vendor/` are gitignored, so real credentials and the mPDF library never end up in this repo.

## About and contact

Built by Isaac Adjei, studying BEng Electronic Engineering and Computer Science at [Aston University](https://www.aston.ac.uk). Find more at [isaacadjei.me](https://isaacadjei.me) or [LinkedIn](https://www.linkedin.com/in/isaacadjei).

For questions or bugs, open an [issue](https://github.com/zaccesss/astoncv/issues) in this repository. Direct contact is available at [contact@isaacadjei.me](mailto:contact@isaacadjei.me) or through the [website contact page](https://isaacadjei.me/contact).

See [NOTICE.md](NOTICE.md) for licensing details on the campus photography and [LICENSE](LICENSE) for the MIT licence covering the project code.
