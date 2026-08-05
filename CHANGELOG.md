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

## [2.2.1] - 2026-08-05

### Security

- Scoped the php-lint workflow token to read-only contents access, closing a CodeQL
  actions/missing-workflow-permissions alert. The job only checks out the repo and runs a
  syntax check, it never writes back.

---

## [Unreleased]

### Updated

- README rewritten to remove animated capsule-render and typing-svg banners, merge the duplicate
  Links and Contact and Support sections into one and reconcile the avatar upload status with
  ROADMAP.md (shipped in v2.0.0, not still planned as this file previously implied)
- ROADMAP.md synced with this file: current version corrected from v2.0.0 to v2.2.1 and Completed
  entries added for v2.2.0 and v2.2.1

Planned or in progress - not yet in a release.

- Unit and integration test suite
- Pagination on the CV browse page

---

## [2.2.0] - 2026-05-13

### Added

- `LICENSE` - MIT licence
- `CHANGELOG.md`, `SECURITY.md`, `ROADMAP.md` - standard repo meta files
- `.github/workflows/php-lint.yml` - CI that runs PHP syntax check on push and PR to main
- `.github/PULL_REQUEST_TEMPLATE.md` and `.github/ISSUE_TEMPLATE/bug_report.md`
- Aston University SVG favicon on all pages

### Updated

- Footer on all pages - removed student ID and module code, replaced with Aston University link
- Student section in README renamed to About, student ID and module code removed
- README setup instructions updated to reflect deleted `sql/cvs.sql`
- All form placeholders replaced with natural descriptive text across `index.php`, `login.php`, `register.php` and `update.php`
- Student ID removed from generated PDF footer in `export_cv.php`
- Contact email updated from `contact@zacess.com` to `contact@isaacadjei.me` throughout

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
