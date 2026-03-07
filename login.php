<?php
/*
 * login.php
 * Login Page - AstonCV
 * Student: Isaac Adjei (240191278)
 *
 * Allows registered users to log in to their account.
 * Uses PHP sessions to remember who is logged in across pages.
 * Passwords are verified against the hashed version stored in the database.
 * CSRF token validates that the form was submitted from this site only.
 * Brute force protection - account locked after 5 failed attempts.
 */

// Loads the database connection
require 'db.php';

// Starts the session to check login state and store CSRF token
session_start();

// Redirects to update.php if the user is already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: update.php');
    exit;
}

// Generates a CSRF token if one does not already exist in the session
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Initialises the failed login counter in the session if not already set
// This tracks how many times the user has failed to log in
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
}

// Initialises the lockout time - set when the user gets locked out
if (!isset($_SESSION['lockout_time'])) {
    $_SESSION['lockout_time'] = null;
}

// Stores any error message to display back to the user
$error = '';

// Checks if the account is currently locked out
// If locked out less than 15 minutes ago, blocks the login attempt
$lockedOut = false;
if ($_SESSION['lockout_time'] !== null) {
    $secondsElapsed = time() - $_SESSION['lockout_time'];
    if ($secondsElapsed < 900) {
        // 900 seconds = 15 minutes
        $minutesLeft = ceil((900 - $secondsElapsed) / 60);
        $error = "Too many failed attempts. Please try again in {$minutesLeft} minute(s).";
        $lockedOut = true;
    } else {
        // Lockout period has expired so resets the counters
        $_SESSION['login_attempts'] = 0;
        $_SESSION['lockout_time']   = null;
    }
}

// Checks if the form has been submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$lockedOut) {

    // Validates the CSRF token before processing the login
    if (!isset($_POST['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("Invalid CSRF token. Please go back and try again.");
    }

    // Retrieves and trims the submitted email and password
    $email    = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Validates that both fields were filled in
    if (empty($email) || empty($password)) {
        $error = "Please enter both your email and password.";
    } else {

        // Looks up the user by email using a prepared statement
        $stmt = $pdo->prepare("SELECT * FROM cvs WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // Checks if the user exists and the password matches the stored hash
        if ($user && password_verify($password, $user['password'])) {

            // Resets the failed attempt counter on successful login
            $_SESSION['login_attempts'] = 0;
            $_SESSION['lockout_time']   = null;

            // Stores the user's id and name in the session
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['name'];

            // Handles "Remember Me" - sets a cookie lasting 30 days
            // The cookie stores the user's id so they stay logged in
            if (isset($_POST['remember_me'])) {
                // Creates a secure token to store in the cookie
                $token = bin2hex(random_bytes(32));
                // Stores the token in the session for verification
                $_SESSION['remember_token'] = $token;
                // Sets cookie for 30 days (30 * 24 * 60 * 60 = 2592000 seconds)
                setcookie('remember_me', $user['id'] . ':' . $token, 
                          time() + 2592000, '/', '', false, true);
            }

            header('Location: update.php');
            exit;

        } else {
            // Increments the failed attempt counter
            $_SESSION['login_attempts']++;

            // Locks the account after 5 failed attempts
            if ($_SESSION['login_attempts'] >= 5) {
                $_SESSION['lockout_time'] = time();
                $error = "Too many failed attempts. Please try again in 15 minutes.";
            } else {
                // Shows how many attempts are remaining
                $remaining = 5 - $_SESSION['login_attempts'];
                $error = "Invalid email or password. {$remaining} attempt(s) remaining.";
            }
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
    <link rel="stylesheet" href="style.css">
</head>
<body>

<!-- Sticky navigation header -->
<header id="main-header">
    <div class="container">
        <div class="header-content">
            <a href="index.php" style="text-decoration: none;">
                <img src="logo.svg" alt="Aston University" style="height: 50px; width: auto;">
            </a>
            <nav>
                <ul>
                    <li><a href="index.php">Browse CVs</a></li>
                    <li><a href="register.php">Register</a></li>
                    <li><a href="login.php" class="active">Login</a></li>
                </ul>
            </nav>
        </div>
    </div>
</header>

<!-- Hero banner -->
<section class="hero">
    <div class="container">
        <div class="hero-content">
            <h1>Welcome Back</h1>
            <p class="subtitle">Log in to manage your CV</p>
        </div>
    </div>
</section>

<!-- Login form section -->
<section class="section">
    <div class="container">
        <div class="form-card">

            <!-- Shows error message if login failed or account is locked -->
            <?php if ($error): ?>
                <div class="alert-error">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <!-- Login form - posts back to this same page -->
            <!-- Disables the form if the account is locked out -->
            <form method="POST" action="login.php">

                <!-- CSRF hidden field -->
                <input type="hidden" name="csrf_token"
                       value="<?php echo $_SESSION['csrf_token']; ?>">

                <!-- Email field -->
                <div class="form-group">
                    <label for="email">Email Address <span class="required">*</span></label>
                    <input type="email" id="email" name="email"
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                           placeholder="e.g. yourname@aston.ac.uk"
                           <?php echo $lockedOut ? 'disabled' : ''; ?>>
                </div>

                <!-- Password field with show/hide toggle -->
                <div class="form-group">
                    <label for="password">Password <span class="required">*</span></label>
                    <div class="password-wrapper">
                        <input type="password" id="password" name="password"
                               placeholder="Enter your password"
                               <?php echo $lockedOut ? 'disabled' : ''; ?>>
                        <!-- Eye icon toggle button -->
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
                </div>

                <!-- Remember me checkbox -->
                <div class="form-group" style="display: flex; align-items: center; gap: 0.75rem;">
                    <input type="checkbox" id="remember_me" name="remember_me"
                           style="width: auto; cursor: pointer; accent-color: var(--primary-color);">
                    <label for="remember_me" style="margin: 0; font-weight: 400; cursor: pointer;">
                        Remember me 
                    </label>
                </div>

                <!-- Submit button - greyed out if locked -->
                <button type="submit" class="submit-button"
                        <?php echo $lockedOut ? 'disabled' : ''; ?>>
                    Login
                </button>

            </form>

            <p style="text-align: center; margin-top: 1.5rem; color: var(--text-medium);">
                Don't have an account?
                <a href="register.php" style="color: var(--primary-color); font-weight: 600;">Register here</a>
            </p>

        </div>
    </div>
</section>

<footer>
    <div class="container">
        <p>&copy; 2026 AstonCV</p>
    </div>
</footer>

<!-- Password show/hide toggle JavaScript -->
<script>
/*
 * togglePassword()
 * Shows or hides the password text in a password field
 * Changes the eye icon between open and closed depending on state
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
</script>

</body>
</html>