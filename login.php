<?php
/*
 * login.php
 * Login Page - AstonCV
 * Student: Isaac Adjei (240191278)
 *
 * I allow registered users to log in to their account.
 * I use PHP sessions to remember who is logged in across pages.
 * I verify passwords against the hashed version stored in the database.
 * I use a CSRF token to prevent cross-site request forgery attacks.
 * I lock the account for 15 minutes after 5 failed login attempts.
 * I wrap database calls in try/catch for proper error handling.
 */

require 'db.php';
session_start();

// I redirect to dashboard if the user is already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

// I generate a CSRF token if one does not already exist
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// I initialise the failed login counter if not already set
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
}

if (!isset($_SESSION['lockout_time'])) {
    $_SESSION['lockout_time'] = null;
}

$error     = '';
$lockedOut = false;

// I check if the account is currently locked out
if ($_SESSION['lockout_time'] !== null) {
    $secondsElapsed = time() - $_SESSION['lockout_time'];
    if ($secondsElapsed < 900) {
        $minutesLeft = ceil((900 - $secondsElapsed) / 60);
        $error       = "Too many failed attempts. Please try again in {$minutesLeft} minute(s).";
        $lockedOut   = true;
    } else {
        // I reset counters once the 15-minute lockout expires
        $_SESSION['login_attempts'] = 0;
        $_SESSION['lockout_time']   = null;
    }
}

// I process the form only if it was submitted and the account is not locked
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$lockedOut) {

    // I validate the CSRF token before doing anything else
    if (!isset($_POST['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("Invalid CSRF token. Please go back and try again.");
    }

    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($email) || empty($password)) {
        $error = "Please enter both your email and password.";
    } else {

        try {
            // I look up the user by email using a prepared statement
            $stmt = $pdo->prepare("SELECT * FROM cvs WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            // I verify the password against the stored hash
            if ($user && password_verify($password, $user['password'])) {

                // I reset the failed attempt counter on success
                $_SESSION['login_attempts'] = 0;
                $_SESSION['lockout_time']   = null;

                // I store the user's id and name in the session
                $_SESSION['user_id']   = $user['id'];
                $_SESSION['user_name'] = $user['name'];

                // I set a 30-day remember me cookie if the checkbox was ticked
                if (isset($_POST['remember_me'])) {
                    $token = bin2hex(random_bytes(32));
                    $_SESSION['remember_token'] = $token;
                    setcookie('remember_me', $user['id'] . ':' . $token,
                              time() + 2592000, '/', '', false, true);
                }

                // I redirect to the dashboard after a successful login
                header('Location: dashboard.php');
                exit;

            } else {
                // I increment the failed attempt counter
                $_SESSION['login_attempts']++;

                if ($_SESSION['login_attempts'] >= 5) {
                    $_SESSION['lockout_time'] = time();
                    $error = "Too many failed attempts. Please try again in 15 minutes.";
                    $lockedOut = true;
                } else {
                    $remaining = 5 - $_SESSION['login_attempts'];
                    $error     = "Invalid email or password. {$remaining} attempt(s) remaining.";
                }
            }

        } catch (PDOException $e) {
            $error = "Something went wrong. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - AstonCV</title>
    <link rel="icon" type="image/svg+xml" href="images/logo.svg">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<!-- ============================================================
     HEADER — Same dark navbar as every other page
     ============================================================ -->
<header id="main-header">
    <div class="header-content">
        <a href="index.php" style="text-decoration: none; display: flex; align-items: center;">
            <img src="images/logo.svg" alt="Aston University" class="logo">
        </a>
        <nav>
            <ul>
                <li><a href="index.php">Browse CVs</a></li>
                <li><a href="register.php">Register</a></li>
                <li><a href="login.php" class="active">Login</a></li>
            </ul>
        </nav>
    </div>
</header>

<!-- ============================================================
     SPLIT LAYOUT — Campus photo on the left, form on the right.
     On mobile the photo is hidden and only the form shows.
     ============================================================ -->
<div class="split-layout">

    <!-- Left: campus photo with text overlay -->
    <div class="split-image">
        <h2>Welcome back to AstonCV</h2>
        <p>Log in to update your CV, track views and manage your profile.</p>
    </div>

    <!-- Right: login form -->
    <div class="split-form">
        <div class="form-card">

            <h2>Log In</h2>
            <p class="form-subtitle">Enter your details below to access your account.</p>

            <!-- I show an error if login failed or account is locked -->
            <?php if ($error): ?>
                <div class="alert-error">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <!-- Login form posts back to this same page -->
            <form method="POST" action="login.php">

                <!-- I include the CSRF token as a hidden field -->
                <input type="hidden" name="csrf_token"
                       value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

                <!-- Email -->
                <div class="form-group">
                    <label for="email">Email Address <span class="required">*</span></label>
                    <input type="email"
                           id="email"
                           name="email"
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                           placeholder="e.g. yourname@aston.ac.uk"
                           autocomplete="email"
                           <?php echo $lockedOut ? 'disabled' : ''; ?>>
                </div>

                <!-- Password with show/hide toggle -->
                <div class="form-group">
                    <label for="password">Password <span class="required">*</span></label>
                    <div class="password-wrapper">
                        <input type="password"
                               id="password"
                               name="password"
                               placeholder="Enter your password"
                               autocomplete="current-password"
                               <?php echo $lockedOut ? 'disabled' : ''; ?>>
                        <!-- I use an SVG eye icon for the toggle button -->
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
                </div>

                <!-- Remember me -->
                <div class="form-group" style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1.75rem;">
                    <input type="checkbox"
                           id="remember_me"
                           name="remember_me"
                           style="width: auto; cursor: pointer; accent-color: var(--primary-color);">
                    <label for="remember_me" style="margin: 0; font-weight: 400; font-size: 0.9rem; cursor: pointer;">
                        Remember me
                    </label>
                </div>

                <!-- Submit — greyed out if account is locked -->
                <button type="submit"
                        class="submit-button"
                        <?php echo $lockedOut ? 'disabled' : ''; ?>>
                    Login
                </button>

            </form>

            <p style="text-align: center; margin-top: 1.5rem; font-size: 0.9rem; color: var(--text-light);">
                Don't have an account?
                <a href="register.php" style="color: var(--primary-color); font-weight: 600;">Register here</a>
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
                <li><a href="https://github.com/zaccesss/astoncv" target="_blank" rel="noopener">GitHub Repository</a></li>
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
        // I swap the eye icon to the crossed-out version
        icon.innerHTML = `
            <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8
                     a18.45 18.45 0 0 1 5.06-5.94"/>
            <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8
                     a18.5 18.5 0 0 1-2.16 3.19"/>
            <line x1="1" y1="1" x2="23" y2="23"/>`;
    } else {
        field.type = 'password';
        // I swap back to the open eye icon
        icon.innerHTML = `
            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
            <circle cx="12" cy="12" r="3"/>`;
    }
}
</script>

</body>
</html>