<p align="center">
  <img src="https://capsule-render.vercel.app/api?type=waving&color=gradient&height=120&section=header&text=AstonCV&fontSize=48&fontAlignY=32&fontColor=ffffff&animation=fadeIn" />
</p>

<p align="center">
  <img src="https://readme-typing-svg.demolab.com?font=Fira+Code&size=22&pause=1200&color=0066CC&center=true&vCenter=true&width=600&height=55&lines=Full-Stack+CV+Database+Website;PHP+%7C+MySQL+%7C+Custom+CSS;DG1IAD+Portfolio+3+-+Aston+University;Secure+%7C+Responsive+%7C+Deployed" />
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Aston_University-DG1IAD_Portfolio_3-purple?style=for-the-badge&logo=academia" />
  <img src="https://img.shields.io/badge/PHP-8.2-777BB4?style=for-the-badge&logo=php&logoColor=white" />
  <img src="https://img.shields.io/badge/MySQL-Database-4479A1?style=for-the-badge&logo=mysql&logoColor=white" />
  <img src="https://img.shields.io/badge/Status-Live-brightgreen?style=for-the-badge" />
</p>

---

## Links

<p align="center">
  <a href="http://240191278.cs2410-web01pvm.aston.ac.uk">
    <img src="https://img.shields.io/badge/Live_Site-AstonCV-0066CC?style=for-the-badge&logo=google-chrome&logoColor=white">
  </a>
  <a href="https://www.linkedin.com/in/isaacadjei">
    <img src="https://img.shields.io/badge/LinkedIn-Isaac_Adjei-0a66c2?style=for-the-badge&logo=linkedin&logoColor=white">
  </a>
  <a href="mailto:contact@zacess.com">
    <img src="https://img.shields.io/badge/Email-Contact-ff6f61?style=for-the-badge&logo=gmail&logoColor=white">
  </a>
</p>

---

## Quick Navigation

<p align="center">
  🔎 •
  <a href="#overview">Overview</a> •
  <a href="#live-site">Live Site</a> •
  <a href="#features">Features</a> •
  <a href="#security">Security</a> •
  <a href="#tech-stack">Tech Stack</a> •
  <a href="#setup">Setup</a> •
  <a href="#student">Student</a>
</p>

---

<a id="overview"></a>
## Overview

AstonCV is a full-stack CV database website built for **DG1IAD Portfolio 3** at Aston University. It allows users to browse and search student CVs publicly, register an account, and manage their own CV once logged in.

The project is built with plain PHP 8.2 and MySQL - no frameworks - and is deployed on the Aston University internal hosting server.

---

<a id="live-site"></a>
## Live Site

🌐 **http://240191278.cs2410-web01pvm.aston.ac.uk**

---

<a id="features"></a>
## Features

### Public (no login required)
- Browse all CVs as cards on the homepage
- Search CVs by name or key programming language
- View full CV details including skills, education, work experience and links
- Register a new account

### Registered Users
- Login and logout securely
- Update CV details, profile picture and password
- Profile picture upload (jpg/png/gif/webp, max 2MB)

### Extra Features
- View counter on every CV
- Skills displayed as pill badges
- Password strength checker with live rules checklist
- Show/hide password toggle
- Remember Me (30-day cookie)

---

<a id="security"></a>
## Security Measures

| # | Measure | Implementation |
|---|---------|----------------|
| 1 | XSS Prevention | `htmlspecialchars()` on all output |
| 2 | SQL Injection Prevention | PDO prepared statements on all queries |
| 3 | Password Hashing | bcrypt via `password_hash()` |
| 4 | Password Verification | `password_verify()` on login |
| 5 | Session Authentication | `$_SESSION['user_id']` checked on protected pages |
| 6 | Authorisation | Users can only edit their own CV |
| 7 | Server-side Validation | All form fields validated in PHP |
| 8 | Vague Error Messages | Login always returns generic error |
| 9 | Session Destruction | `session_unset()` and `session_destroy()` on logout |
| 10 | CSRF Protection | Hidden token validated on every POST form |
| 11 | Brute-force Protection | Account locked 15 mins after 5 failed login attempts |

---

<a id="tech-stack"></a>
## Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | PHP 8.2 (no framework) |
| Database | MySQL |
| Frontend | HTML5, CSS3, JavaScript |
| Deployment | Aston University internal server (Apache) |
| Version Control | Git & GitHub |

---

<a id="setup"></a>
## Local Setup

1. Clone the repo into `C:\xampp\htdocs\astoncv`
2. Copy `config.example.php` to `config.php` and fill in your database credentials
3. Import `cvs.sql` into MySQL via phpMyAdmin
4. Start Apache and MySQL in XAMPP
5. Visit `http://localhost/astoncv`

> `config.php` is gitignored - your real credentials are never stored in this repo.

---

<a id="student"></a>
## Student

**Isaac Adjei**  
Student ID: 240191278  
BEng Electronic Engineering and Computer Science  
Aston University - DG1IAD Portfolio 3

---

<p align="center">
  <img src="https://capsule-render.vercel.app/api?type=waving&color=gradient&height=80&section=footer" />
</p>
