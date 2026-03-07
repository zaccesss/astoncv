<?php
/*
 * config.example.php
 * Example credentials file for AstonCV
 * Student: Isaac Adjei (240191278)
 *
 * Copy this file, rename it to config.php, and fill in real credentials.
 * config.php is gitignored and must never be committed to the repository.
 *
 * For XAMPP: dbname = "astoncv", username = "root", password = ""
 * For Aston server: use the credentials from my welcome email
 */

$host     = "localhost";
$dbname   = "YOUR_DB_NAME";
$username = "YOUR_DB_USERNAME";
$password = "YOUR_DB_PASSWORD";
?>
```

**`.gitignore`** — tells Git to never upload config.php:
```
# Database credentials - must never be committed to GitHub
config.php

# Uploaded profile pictures - too large for GitHub
uploads/