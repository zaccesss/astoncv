# Security Policy

## Supported Versions

AstonCV is a university portfolio project. Only the latest version on `main` is maintained.

| Version | Supported |
| ------- | --------- |
| Latest  | Yes       |
| Older   | No        |

---

## Reporting a Vulnerability

> [!WARNING]
> Do not open a public GitHub issue for security concerns.

If you find a security vulnerability - such as an XSS flaw, SQL injection risk, session handling issue or accidentally committed credentials - please report it privately.

Contact: **Isaac Adjei** via [isaacadjei.me](https://isaacadjei.me)

Please include:

- A clear description of the vulnerability
- The file and line number where it was found
- Steps to reproduce it
- Any suggested fix if you have one

---

## What to Expect

- Acknowledgement within 72 hours
- Investigation and a timeline for a fix
- Credit in the fix commit unless you prefer to remain anonymous

---

## Scope

| In scope                                      | Out of scope                              |
| --------------------------------------------- | ----------------------------------------- |
| XSS, SQL injection or CSRF vulnerabilities    | Style or UI preferences                   |
| Session or authentication flaws               | Feature requests                          |
| Accidentally committed secrets or credentials | Typos or wording (open a normal issue)    |
| Insecure direct object reference issues       | Local environment setup problems          |

---

## Security Measures in This Project

AstonCV implements the following security controls:

- Prepared statements for all database queries (PDO)
- CSRF token validation on all forms
- Password hashing with `password_hash` and `password_verify`
- Account lockout after five failed login attempts (15-minute cooldown)
- Output sanitised with `htmlspecialchars` throughout
- POST-only enforcement on form handlers
- Honeypot field on the contact form to block spam bots
- Input validation at all system boundaries

---

<div align="center">

Made with care by [Isaac Adjei](https://isaacadjei.me)

</div>
