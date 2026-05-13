<?php
/*
 * register.php
 * Registration Page - AstonCV
 * Student: Isaac Adjei (240191278)
 *
 * I allow new users to create an account and add their CV.
 * I validate all fields server-side before saving to the database.
 * I hash passwords using bcrypt — never stored as plain text.
 * I use a CSRF token to prevent cross-site request forgery.
 * I enforce password strength: uppercase, number, special character.
 * I wrap all database calls in try/catch for proper error handling.
 */

require 'db.php';
session_start();

// I redirect to dashboard if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

// I generate a CSRF token if one does not already exist
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$errors  = [];
$success = '';

// I preserve submitted values so the form refills on error
$old = [
    'name'            => '',
    'email'           => '',
    'keyprogramming'  => '',
    'skills'          => '',
    'profile'         => '',
    'education'       => '',
    'work_experience' => '',
    'URLlinks'        => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // I validate the CSRF token before processing anything
    if (!isset($_POST['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("Invalid CSRF token. Please go back and try again.");
    }

    // I retrieve and trim all submitted fields
    $name            = trim($_POST['name']            ?? '');
    $email           = trim($_POST['email']           ?? '');
    $password        = trim($_POST['password']        ?? '');
    $keyprogramming  = trim($_POST['keyprogramming']  ?? '');
    $skills          = trim($_POST['skills']          ?? '');
    $profile         = trim($_POST['profile']         ?? '');
    $education       = trim($_POST['education']       ?? '');
    $work_experience = trim($_POST['work_experience'] ?? '');
    $URLlinks        = trim($_POST['URLlinks']        ?? '');

    // I keep old values so the form refills if there are errors
    $old = compact('name', 'email', 'keyprogramming', 'skills',
                   'profile', 'education', 'work_experience', 'URLlinks');

    // I validate each required field
    if (empty($name))           $errors[] = "Name is required.";
    if (empty($email))          $errors[] = "Email is required.";
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL))
                                $errors[] = "Please enter a valid email address.";
    if (empty($keyprogramming)) $errors[] = "Key programming language is required.";
    if (empty($profile))        $errors[] = "Profile summary is required.";
    if (empty($education))      $errors[] = "Education is required.";

    // I enforce password strength requirements
    if (empty($password)) {
        $errors[] = "Password is required.";
    } elseif (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters.";
    } elseif (!preg_match('/[A-Z]/', $password)) {
        $errors[] = "Password must contain at least one uppercase letter.";
    } elseif (!preg_match('/[0-9]/', $password)) {
        $errors[] = "Password must contain at least one number.";
    } elseif (!preg_match('/[\W_]/', $password)) {
        $errors[] = "Password must contain at least one special character (e.g. ! @ # $).";
    }

    // I only save to the database if there are no validation errors
    if (empty($errors)) {
        try {
            // I check if this email is already registered
            $stmt = $pdo->prepare("SELECT id FROM cvs WHERE email = ?");
            $stmt->execute([$email]);

            if ($stmt->fetch()) {
                $errors[] = "An account with that email already exists.";
            } else {
                // I hash the password before storing — never plain text
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                // I insert the new user into the database
                $stmt = $pdo->prepare(
                    "INSERT INTO cvs
                     (name, email, password, keyprogramming, skills, profile,
                      education, work_experience, URLlinks)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
                );
                $stmt->execute([
                    $name, $email, $hashedPassword, $keyprogramming,
                    $skills, $profile, $education, $work_experience, $URLlinks
                ]);

                $success = "Account created! You can now log in.";
                // I clear the old values after a successful registration
                $old = array_map(fn($v) => '', $old);
            }

        } catch (PDOException $e) {
            $errors[] = "Something went wrong. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - AstonCV</title>
    <link rel="icon" type="image/svg+xml" href="images/logo.svg">
    <link rel="stylesheet" href="style.css">
    <style>
        /* I add password strength bar styles specific to this page */
        .password-strength {
            height: 4px;
            background: var(--border-color);
            border-radius: 2px;
            margin-top: 0.6rem;
            overflow: hidden;
        }

        .strength-fill {
            height: 100%;
            width: 0%;
            border-radius: 2px;
            transition: width 0.3s ease, background 0.3s ease;
        }

        .strength-text {
            font-size: 0.78rem;
            font-weight: 600;
            margin-top: 0.3rem;
            display: block;
        }

        /* I style the password rule checklist */
        .password-rules {
            list-style: none;
            padding: 0;
            margin: 0.75rem 0 0;
            display: flex;
            flex-direction: column;
            gap: 0.3rem;
        }

        .password-rules li {
            font-size: 0.82rem;
            color: var(--text-light);
            transition: color 0.2s ease;
        }

        /* I make the register split image use the library photo */
        .split-image--register {
            background:
                linear-gradient(160deg, rgba(26,10,46,0.72) 0%, rgba(92,45,130,0.62) 100%),
                url('images/campus-library.jpg') center / cover no-repeat;
        }

        /* I add a section label style for the form groups */
        .form-section-label {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            color: var(--primary-color);
            margin: 1.75rem 0 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid var(--border-color);
            display: block;
        }

        /* I make the register form card wider since there are many fields */
        .register-form-area {
            width: 100%;
            max-width: 560px;
            padding: 2.5rem 1.5rem;
            overflow-y: auto;
        }

        /* I override split-form padding for the register page */
        .split-form--register {
            padding: 2rem 1.5rem;
            align-items: flex-start;
            overflow-y: auto;
            max-height: 100vh;
        }
    </style>
</head>
<body>

<!-- ============================================================
     HEADER
     ============================================================ -->
<header id="main-header">
    <div class="header-content">
        <a href="index.php" style="text-decoration: none; display: flex; align-items: center;">
            <img src="images/logo.svg" alt="Aston University" class="logo">
        </a>
        <nav>
            <ul>
                <li><a href="index.php">Browse CVs</a></li>
                <li><a href="register.php" class="active">Register</a></li>
                <li><a href="login.php">Login</a></li>
            </ul>
        </nav>
    </div>
</header>

<!-- ============================================================
     SPLIT LAYOUT — Library photo left, registration form right
     ============================================================ -->
<div class="split-layout">

    <!-- Left: campus library photo with overlay text -->
    <div class="split-image split-image--register">
        <h2>Join AstonCV</h2>
        <p>
            Create your profile, showcase your skills and connect with
            opportunities as an Aston University programmer.
        </p>
    </div>

    <!-- Right: registration form -->
    <div class="split-form split-form--register">
        <div class="register-form-area">

            <h2 style="font-size: 1.6rem; color: var(--text-dark); margin-bottom: 0.4rem; letter-spacing: -0.02em;">
                Create Account
            </h2>
            <p style="color: var(--text-light); font-size: 0.88rem; margin-bottom: 1.5rem;">
                Fill in your details to add your CV to AstonCV.
            </p>

            <!-- Success message -->
            <?php if ($success): ?>
                <div class="alert-success">
                    <?php echo htmlspecialchars($success); ?>
                    <a href="login.php" style="font-weight: 700; margin-left: 0.5rem;">Login here &rarr;</a>
                </div>
            <?php endif; ?>

            <!-- Error messages -->
            <?php if (!empty($errors)): ?>
                <div class="alert-error">
                    <ul>
                        <?php foreach ($errors as $err): ?>
                            <li><?php echo htmlspecialchars($err); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" action="register.php">

                <!-- I include the CSRF token as a hidden field -->
                <input type="hidden" name="csrf_token"
                       value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

                <!-- ---- ACCOUNT DETAILS ---- -->
                <span class="form-section-label">Account Details</span>

                <div class="form-group">
                    <label for="name">Full Name <span class="required">*</span></label>
                    <input type="text" id="name" name="name"
                           value="<?php echo htmlspecialchars($old['name']); ?>"
                           placeholder="Your full name"
                           autocomplete="name">
                </div>

                <div class="form-group">
                    <label for="email">Email Address <span class="required">*</span></label>
                    <input type="email" id="email" name="email"
                           value="<?php echo htmlspecialchars($old['email']); ?>"
                           placeholder="your@email.com"
                           autocomplete="email">
                </div>

                <div class="form-group">
                    <label for="password">Password <span class="required">*</span></label>
                    <div class="password-wrapper">
                        <input type="password" id="password" name="password"
                               placeholder="Choose a strong password"
                               autocomplete="new-password"
                               oninput="checkPasswordStrength(this.value)">
                        <button type="button"
                                class="password-toggle"
                                onclick="togglePassword('password', 'eyeIcon1')"
                                aria-label="Show or hide password">
                            <svg id="eyeIcon1" xmlns="http://www.w3.org/2000/svg"
                                 width="20" height="20" viewBox="0 0 24 24"
                                 fill="none" stroke="currentColor"
                                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                        </button>
                    </div>
                    <!-- Password strength bar -->
                    <div class="password-strength">
                        <div class="strength-fill" id="strengthFill"></div>
                    </div>
                    <span class="strength-text" id="strengthText"></span>
                    <!-- Password requirements checklist -->
                    <ul class="password-rules">
                        <li id="rule-length">At least 8 characters</li>
                        <li id="rule-upper">At least one uppercase letter</li>
                        <li id="rule-number">At least one number</li>
                        <li id="rule-special">At least one special character (! @ # $ etc.)</li>
                    </ul>
                </div>

                <!-- ---- CV DETAILS ---- -->
                <span class="form-section-label">CV Details</span>

                <div class="form-group">
                    <label for="keyprogramming">Key Programming Language <span class="required">*</span></label>
                    <input type="text" id="keyprogramming" name="keyprogramming"
                           value="<?php echo htmlspecialchars($old['keyprogramming']); ?>"
                           placeholder="Your main language">
                </div>

                <div class="form-group">
                    <label for="skills">
                        Skills &amp; Technologies
                        <span style="color: var(--text-light); font-weight: 400;">(optional)</span>
                    </label>
                    <input type="text" id="skills" name="skills"
                           value="<?php echo htmlspecialchars($old['skills']); ?>"
                           placeholder="Your skills, separated by commas">
                    <span class="field-hint">Separate each skill with a comma</span>
                </div>

                <div class="form-group">
                    <label for="profile">Profile Summary <span class="required">*</span></label>
                    <textarea id="profile" name="profile"
                              placeholder="Tell us about yourself"><?php echo htmlspecialchars($old['profile']); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="education">Education <span class="required">*</span></label>
                    <textarea id="education" name="education"
                              placeholder="Your education history"><?php echo htmlspecialchars($old['education']); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="work_experience">
                        Work Experience
                        <span style="color: var(--text-light); font-weight: 400;">(optional)</span>
                    </label>
                    <textarea id="work_experience" name="work_experience"
                              placeholder="Your work experience"><?php echo htmlspecialchars($old['work_experience']); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="URLlinks">
                        Links
                        <span style="color: var(--text-light); font-weight: 400;">(optional)</span>
                    </label>
                    <input type="text" id="URLlinks" name="URLlinks"
                           value="<?php echo htmlspecialchars($old['URLlinks']); ?>"
                           placeholder="GitHub, LinkedIn or portfolio links">
                </div>

                <button type="submit" class="submit-button">Create Account</button>

            </form>

            <p style="text-align: center; margin-top: 1.5rem; font-size: 0.9rem; color: var(--text-light);">
                Already have an account?
                <a href="login.php" style="color: var(--primary-color); font-weight: 600;">Login here</a>
            </p>

        </div>
    </div>
</div>

<!-- ============================================================
     FOOTER
     ============================================================ -->
<footer>
    <div class="footer-grid">
        <div class="footer-brand">
            <img src="images/logo.svg" alt="Aston University" class="footer-logo">
            <p>
                AstonCV is a CV database platform built for Aston University students
                to showcase their programming skills and experience.
            </p>
        </div>
        <div class="footer-col">
            <h4>Navigate</h4>
            <ul>
                <li><a href="index.php">Browse CVs</a></li>
                <li><a href="register.php">Register</a></li>
                <li><a href="login.php">Login</a></li>
            </ul>
        </div>
        <div class="footer-col">
            <h4>Project Info</h4>
            <ul>
                <li><a href="https://github.com/zaccessss/astoncv" target="_blank" rel="noopener">GitHub Repository</a></li>
                <li><a href="http://240191278.cs2410-web01pvm.aston.ac.uk" target="_blank" rel="noopener">Live Site</a></li>
                <li><a href="https://www.aston.ac.uk" target="_blank" rel="noopener">Aston University</a></li>
            </ul>
        </div>
    </div>
    <div class="footer-bottom">
        <p>&copy; 2026 AstonCV - <a href="https://www.aston.ac.uk" target="_blank" rel="noopener noreferrer">Aston University</a> - Isaac Adjei</p>
    </div>
</footer>

<script>
// I add the scrolled class to the navbar when the user scrolls
const header = document.getElementById('main-header');
window.addEventListener('scroll', function () {
    header.classList.toggle('scrolled', window.scrollY > 30);
});

// I toggle the password field between hidden and visible
function togglePassword(fieldId, iconId) {
    const field = document.getElementById(fieldId);
    const icon  = document.getElementById(iconId);
    if (field.type === 'password') {
        field.type = 'text';
        icon.innerHTML = `
            <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8
                     a18.45 18.45 0 0 1 5.06-5.94"/>
            <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8
                     a18.5 18.5 0 0 1-2.16 3.19"/>
            <line x1="1" y1="1" x2="23" y2="23"/>`;
    } else {
        field.type = 'password';
        icon.innerHTML = `
            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
            <circle cx="12" cy="12" r="3"/>`;
    }
}

// I run this every time the user types in the password field
function checkPasswordStrength(value) {
    const hasLength  = value.length >= 8;
    const hasUpper   = /[A-Z]/.test(value);
    const hasNumber  = /[0-9]/.test(value);
    const hasSpecial = /[\W_]/.test(value);

    updateRule('rule-length',  hasLength);
    updateRule('rule-upper',   hasUpper);
    updateRule('rule-number',  hasNumber);
    updateRule('rule-special', hasSpecial);

    const score  = [hasLength, hasUpper, hasNumber, hasSpecial].filter(Boolean).length;
    const fill   = document.getElementById('strengthFill');
    const text   = document.getElementById('strengthText');

    const levels = [
        { width: '0%',   bg: '',          label: '',        color: '' },
        { width: '25%',  bg: '#dc2626',   label: 'Weak',    color: '#dc2626' },
        { width: '50%',  bg: '#f59e0b',   label: 'Fair',    color: '#f59e0b' },
        { width: '75%',  bg: '#3b82f6',   label: 'Good',    color: '#3b82f6' },
        { width: '100%', bg: '#059669',   label: 'Strong',  color: '#059669' },
    ];

    fill.style.width      = levels[score].width;
    fill.style.background = levels[score].bg;
    text.textContent      = levels[score].label;
    text.style.color      = levels[score].color;
}

// I update a single rule item to show a tick or cross
function updateRule(id, passed) {
    const el   = document.getElementById(id);
    const text = el.textContent.replace(/^[✓✗] /, '');
    el.textContent      = (passed ? '✓ ' : '✗ ') + text;
    el.style.color      = passed ? '#059669' : 'var(--text-light)';
    el.style.fontWeight = passed ? '600' : '400';
}
</script>

</body>
</html>