<?php
/*
 * register.php
 * Registration Page - AstonCV
 * Student: Isaac Adjei (240191278)
 *
 * Allows new users to create an account.
 * Validates all fields server-side before saving to the database.
 * Passwords are hashed using bcrypt before storage - never stored as plain text.
 * CSRF token validates that the form was submitted from this site only.
 * Password must meet strength requirements - uppercase, number, special character.
 */

require 'db.php';
session_start();

// Generates a CSRF token if one does not already exist
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$errors  = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Validates the CSRF token before processing anything
    if (!isset($_POST['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("Invalid CSRF token. Please go back and try again.");
    }

    // Retrieves and trims all form fields
    $name            = trim($_POST['name']);
    $email           = trim($_POST['email']);
    $password        = trim($_POST['password']);
    $keyprogramming  = trim($_POST['keyprogramming']);
    $skills          = trim($_POST['skills']);
    $profile         = trim($_POST['profile']);
    $education       = trim($_POST['education']);
    $work_experience = trim($_POST['work_experience']);
    $URLlinks        = trim($_POST['URLlinks']);

    // Validates required fields
    if (empty($name)) {
        $errors[] = "Name is required.";
    }

    if (empty($email)) {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }

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

    if (empty($keyprogramming)) {
        $errors[] = "Key programming language is required.";
    }

    if (empty($profile)) {
        $errors[] = "Profile summary is required.";
    }

    if (empty($education)) {
        $errors[] = "Education is required.";
    }

    // Only saves to database if no validation errors
    if (empty($errors)) {

        // Checks if the email is already registered
        $stmt = $pdo->prepare("SELECT id FROM cvs WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            $errors[] = "An account with that email already exists.";
        } else {

            // Hashes the password using bcrypt before storing
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Inserts the new user including skills and work experience
            $stmt = $pdo->prepare("INSERT INTO cvs 
                (name, email, password, keyprogramming, skills, profile, education, work_experience, URLlinks) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

            $stmt->execute([
                $name,
                $email,
                $hashedPassword,
                $keyprogramming,
                $skills,
                $profile,
                $education,
                $work_experience,
                $URLlinks
            ]);

            $success = "Account created successfully! You can now log in.";
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
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header id="main-header">
    <div class="container">
        <div class="header-content">
            <a href="index.php" style="text-decoration: none;">
                <img src="logo.svg" alt="Aston University" style="height: 50px; width: auto;">
            </a>
            <nav>
                <ul>
                    <li><a href="index.php">Browse CVs</a></li>
                    <li><a href="register.php" class="active">Register</a></li>
                    <li><a href="login.php">Login</a></li>
                </ul>
            </nav>
        </div>
    </div>
</header>

<section class="hero">
    <div class="container">
        <div class="hero-content">
            <h1>Create Account</h1>
            <p class="subtitle">Join AstonCV and showcase your skills</p>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="form-card">

            <?php if ($success): ?>
                <div class="alert-success">
                    <?php echo htmlspecialchars($success); ?>
                    <a href="login.php">Login here</a>
                </div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="alert-error">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" action="register.php">

                <input type="hidden" name="csrf_token"
                       value="<?php echo $_SESSION['csrf_token']; ?>">

                <!-- Name field -->
                <div class="form-group">
                    <label for="name">Full Name <span class="required">*</span></label>
                    <input type="text" id="name" name="name"
                           value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>"
                           placeholder="e.g. Isaac Adjei">
                </div>

                <!-- Email field -->
                <div class="form-group">
                    <label for="email">Email Address <span class="required">*</span></label>
                    <input type="email" id="email" name="email"
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                           placeholder="e.g. yourname@aston.ac.uk">
                </div>

                <!-- Password field with strength checker -->
                <div class="form-group">
                    <label for="password">Password <span class="required">*</span></label>
                    <div class="password-wrapper">
                        <input type="password" id="password" name="password"
                               placeholder="Min 8 chars, uppercase, number, special char"
                               oninput="checkPasswordStrength(this.value)">
                        <button type="button" class="toggle-password"
                                onclick="togglePassword('password', 'eyeIcon1')"
                                aria-label="Show or hide password">
                            <svg id="eyeIcon1" xmlns="http://www.w3.org/2000/svg"
                                 width="20" height="20" viewBox="0 0 24 24"
                                 fill="none" stroke="currentColor"
                                 stroke-width="2" stroke-linecap="round"
                                 stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                        </button>
                    </div>
                    <div class="password-strength" id="strengthBar">
                        <div class="strength-fill" id="strengthFill"></div>
                    </div>
                    <small class="strength-text" id="strengthText"></small>
                    <ul class="password-rules">
                        <li id="rule-length">At least 8 characters</li>
                        <li id="rule-upper">At least one uppercase letter</li>
                        <li id="rule-number">At least one number</li>
                        <li id="rule-special">At least one special character (! @ # $ etc.)</li>
                    </ul>
                </div>

                <!-- Key programming language -->
                <div class="form-group">
                    <label for="keyprogramming">Key Programming Language <span class="required">*</span></label>
                    <input type="text" id="keyprogramming" name="keyprogramming"
                           value="<?php echo isset($_POST['keyprogramming']) ? htmlspecialchars($_POST['keyprogramming']) : ''; ?>"
                           placeholder="e.g. Python, C++, Java">
                </div>

                <!-- Skills - new field -->
                <div class="form-group">
                    <label for="skills">Skills & Technologies
                        <span style="color: var(--text-light); font-weight: 400;">(optional)</span>
                    </label>
                    <input type="text" id="skills" name="skills"
                           value="<?php echo isset($_POST['skills']) ? htmlspecialchars($_POST['skills']) : ''; ?>"
                           placeholder="e.g. Python, JavaScript, C++, MySQL, HTML, CSS">
                    <span class="field-hint">Separate each skill with a comma</span>
                </div>

                <!-- Profile summary -->
                <div class="form-group">
                    <label for="profile">Profile Summary <span class="required">*</span></label>
                    <textarea id="profile" name="profile"
                              placeholder="Write a short summary about yourself"><?php echo isset($_POST['profile']) ? htmlspecialchars($_POST['profile']) : ''; ?></textarea>
                </div>

                <!-- Education -->
                <div class="form-group">
                    <label for="education">Education <span class="required">*</span></label>
                    <textarea id="education" name="education"
                              placeholder="e.g. BEng Computer Science, Aston University (2024-present)"><?php echo isset($_POST['education']) ? htmlspecialchars($_POST['education']) : ''; ?></textarea>
                </div>

                <!-- Work experience - new field -->
                <div class="form-group">
                    <label for="work_experience">Work Experience
                        <span style="color: var(--text-light); font-weight: 400;">(optional)</span>
                    </label>
                    <textarea id="work_experience" name="work_experience"
                              placeholder="e.g. Software Intern at XYZ Ltd (2024) - worked on..."><?php echo isset($_POST['work_experience']) ? htmlspecialchars($_POST['work_experience']) : ''; ?></textarea>
                </div>

                <!-- Links -->
                <div class="form-group">
                    <label for="URLlinks">Links
                        <span style="color: var(--text-light); font-weight: 400;">(optional)</span>
                    </label>
                    <input type="text" id="URLlinks" name="URLlinks"
                           value="<?php echo isset($_POST['URLlinks']) ? htmlspecialchars($_POST['URLlinks']) : ''; ?>"
                           placeholder="e.g. https://github.com/yourusername">
                </div>

                <button type="submit" class="submit-button">Create Account</button>

            </form>

            <p style="text-align: center; margin-top: 1.5rem; color: var(--text-medium);">
                Already have an account?
                <a href="login.php" style="color: var(--primary-color); font-weight: 600;">Login here</a>
            </p>

        </div>
    </div>
</section>

<footer>
    <div class="container">
        <p>&copy; 2026 AstonCV</p>
    </div>
</footer>

<script>
/*
 * togglePassword()
 * Shows or hides the password text in a password field
 */
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

/*
 * checkPasswordStrength()
 * Runs every time the user types in the password field
 */
function checkPasswordStrength(value) {
    const hasLength  = value.length >= 8;
    const hasUpper   = /[A-Z]/.test(value);
    const hasNumber  = /[0-9]/.test(value);
    const hasSpecial = /[\W_]/.test(value);

    updateRule('rule-length',  hasLength);
    updateRule('rule-upper',   hasUpper);
    updateRule('rule-number',  hasNumber);
    updateRule('rule-special', hasSpecial);

    const score = [hasLength, hasUpper, hasNumber, hasSpecial].filter(Boolean).length;
    const fill  = document.getElementById('strengthFill');
    const text  = document.getElementById('strengthText');

    const levels = [
        { width: '0%',   bg: '',        label: '',       color: '' },
        { width: '25%',  bg: '#dc2626', label: 'Weak',   color: '#dc2626' },
        { width: '50%',  bg: '#f59e0b', label: 'Fair',   color: '#f59e0b' },
        { width: '75%',  bg: '#3b82f6', label: 'Good',   color: '#3b82f6' },
        { width: '100%', bg: '#059669', label: 'Strong', color: '#059669' },
    ];

    fill.style.width      = levels[score].width;
    fill.style.background = levels[score].bg;
    text.textContent      = levels[score].label;
    text.style.color      = levels[score].color;
}

/*
 * updateRule()
 * Updates a single rule item in the checklist
 */
function updateRule(id, passed) {
    const el   = document.getElementById(id);
    const text = el.textContent.replace(/^[✓✗] /, '');
    el.textContent      = (passed ? '✓ ' : '✗ ') + text;
    el.style.color      = passed ? '#059669' : '#6b7280';
    el.style.fontWeight = passed ? '600' : '400';
}
</script>

</body>
</html>