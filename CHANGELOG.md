# Changelog

All notable changes to AstonCV are documented here.

This project follows [Semantic Versioning](https://semver.org). The format is based on [Keep a Changelog](https://keepachangelog.com).

---

## Version format

```
MAJOR.MINOR.PATCH

MAJOR - significant redesign or full feature overhaul
MINOR - new features or meaningful additions
PATCH - bug fixes, typo corrections, small updates
```

| Tag       | Meaning                                      |
| --------- | -------------------------------------------- |
| `Added`   | New features or files                        |
| `Updated` | Existing functionality improved or expanded  |
| `Fixed`   | Bugs, errors or broken behaviour corrected   |
| `Removed` | Features or files removed                    |
| `Security`| Security improvements or vulnerability fixes |

---

## [Unreleased]

Planned or in progress - not yet in a release.

- Unit and integration test suite
- Avatar upload support
- Pagination on the CV browse page

---

## [2.1.0] - 2026-05-13

### Updated

- Contact email updated from `contact@zacess.com` to `contact@isaacadjei.me` across all pages
- Footer links updated in `contact_handler.php`, `index.php`, `cv.php`, `dashboard.php`, `login.php`, `register.php` and `update.php`

---

## [2.0.0] - 2026-03-20

### Added

- Full dashboard page for authenticated users with CV preview and account management
- Profile photo upload and avatar display across all pages
- PDF export of the CV using mPDF
- View counter on each CV profile

### Updated

- Complete UI redesign with new colour scheme, typography and layout system
- Navigation updated to reflect authenticated vs unauthenticated state
- All page headers and footers standardised

### Security

- CSRF token validation added to all forms
- Account lockout after five failed login attempts with a 15-minute cooldown
- Password hashing with `password_hash` enforced on registration
- All database queries converted to PDO prepared statements
- Output sanitised with `htmlspecialchars` throughout
- POST-only enforcement on `contact_handler.php`
- Honeypot field added to contact form

### Removed

- `sql/cvs.sql` removed from the repository (schema managed separately)

---

## [1.1.0] - 2026-03-07

### Added

- PDF export feature using the mPDF library
- Updated `.gitignore` to exclude generated PDF files and build artefacts

---

## [1.0.0] - 2026-03-07

### Added

- Initial project structure: `index.php`, `cv.php`, `register.php`, `login.php`, `update.php`, `contact_handler.php`, `db.php`, `config.php`
- MySQL database integration via PDO
- CV browse page with search and filter
- User registration and login with session management
- CV creation and update forms
- Contact form with server-side validation
- `README.md` with project overview and setup instructions

---

[Unreleased]: https://github.com/zaccesss/astoncv/compare/v2.1.0...HEAD
[2.1.0]: https://github.com/zaccesss/astoncv/releases/tag/v2.1.0
[2.0.0]: https://github.com/zaccesss/astoncv/releases/tag/v2.0.0
[1.1.0]: https://github.com/zaccesss/astoncv/releases/tag/v1.1.0
[1.0.0]: https://github.com/zaccesss/astoncv/releases/tag/v1.0.0
