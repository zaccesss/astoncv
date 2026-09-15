# Documentation: AstonCV

Full feature list and security implementation detail for AstonCV. The [README](README.md) stays a short overview, this file covers everything else.

> Back to [README.md](README.md) &nbsp;|&nbsp; [SECURITY.md](SECURITY.md) &nbsp;|&nbsp; [CHANGELOG.md](CHANGELOG.md)

---

## Table of Contents

- [1. Public Features](#1-public-features)
- [2. Registered User Features](#2-registered-user-features)
- [3. UI and Design](#3-ui-and-design)
- [4. Security Controls](#4-security-controls)
- [5. Local Setup](#5-local-setup)

---

## 1. Public Features

- Browse all CVs as cards in a responsive grid with initials avatars
- Search CVs by name or key programming language
- Filter CVs live by programming language with no page reload
- Sort CVs A to Z, Z to A or by most viewed with no page reload
- View full CV details styled as a real CV document with a sidebar
- Download any CV as a PDF through server-side mPDF generation
- Register a new account with a password strength checker
- Submit a contact form enquiry

---

## 2. Registered User Features

- Log in with CSRF protection and brute force lockout
- Stay signed in with a 30-day Remember Me cookie
- View a personal dashboard with CV preview, completeness score and view stats
- Update CV details, profile picture and password
- Upload a profile picture (JPG, PNG, GIF or WEBP, up to 2MB)
- See an owner-only edit button on the CV detail page

---

## 3. UI and Design

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

## 4. Security Controls

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

See [SECURITY.md](SECURITY.md) for how to report a vulnerability privately.

---

## 5. Local Setup

1. Clone the repository
2. Copy `public/config.example.php` to `public/config.php` and fill in local database credentials
3. Create the MySQL database and set up the schema
4. Run `composer install` from the repository root to install mPDF
5. Point your local server's document root at the `public/` folder
6. Visit the site in your browser

> [!IMPORTANT]
> The database schema is not in this repository. Contact the maintainer for the schema file before step 3 or setup will not get past creating the database.

`public/config.php` and `vendor/` are gitignored, so real credentials and the mPDF library never end up in this repository.
