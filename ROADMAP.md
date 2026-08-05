# Roadmap

This roadmap tracks the development plan for AstonCV.

**Current version: v2.2.1 - Security fix**

---

## Status overview

| Feature                        | Status      |
| ------------------------------ | ----------- |
| Core CV CRUD                   | Complete    |
| User registration and login    | Complete    |
| Session management             | Complete    |
| CSRF protection                | Complete    |
| Account lockout                | Complete    |
| Contact form with honeypot     | Complete    |
| Dashboard                      | Complete    |
| PDF export (mPDF)              | Complete    |
| Avatar upload                  | Complete    |
| View counter                   | Complete    |
| Full UI redesign               | Complete    |
| Contact email updated          | Complete    |

---

## Completed

### v1.0.0 - Initial submission

- Project scaffolding and database schema
- CV browse, create, read and update flows
- User registration, login and sessions
- Contact form with server-side validation

### v1.1.0 - PDF export

- mPDF library integrated for CV PDF generation

### v2.0.0 - Resubmission

- Full UI redesign with new design system
- Dashboard for authenticated users
- CSRF token validation on all forms
- Account lockout after five failed attempts
- PDO prepared statements throughout
- Output sanitisation with `htmlspecialchars`
- POST-only enforcement on form handlers
- Honeypot anti-spam on contact form

### v2.1.0 - Contact email

- Contact email migrated to `isaacadjei.me` domain across all pages

### v2.2.0 - Repo meta and CI

- `LICENSE`, `CHANGELOG.md`, `SECURITY.md` and `ROADMAP.md` added as standard repo meta files
- `.github/workflows/php-lint.yml` added as CI, running a PHP syntax check on push and pull request to main
- `.github/PULL_REQUEST_TEMPLATE.md` and `.github/ISSUE_TEMPLATE/bug_report.md` added
- Aston University SVG favicon added on all pages
- Footer, form placeholders and README updated to remove leftover student ID and module code references

### v2.2.1 - Security fix

- Scoped the php-lint workflow token to read-only contents access, closing a CodeQL missing workflow permissions alert

---

## Planned

These items are planned but have no committed timeline.

- Unit and integration test suite
- Pagination on the CV browse page
- Admin panel for user management
- Email delivery confirmation via SMTP (replacing `mail()`)
- Rate limiting on the contact form endpoint
- Accessibility audit and ARIA improvements

---

## Contributing

This is a solo university portfolio project. If you spot a bug or have a suggestion, open an issue on [GitHub](https://github.com/zaccesss/astoncv/issues).
