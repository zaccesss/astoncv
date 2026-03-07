<?php
/*
 * update.php
 * Update CV Page - AstonCV
 * Student: Isaac Adjei (240191278)
 *
 * Allows a logged-in user to update their own CV details.
 * Also allows the user to change their password securely.
 * Supports profile picture upload, skills and work experience.
 * Only accessible to logged-in users - redirects to login if not authenticated.
 * CSRF token validates that the form was submitted from this site only.
 * Password change enforces strength rules and prevents reuse of current password.
 */

// Loads the database connection
require 'db.php';

// Starts the session to check who is logged in
session_start();

// Checks if the user is logged in - redirects to login if not
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Generates a CSRF token if one does not already exist
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$userId          = $_SESSION['user_id'];
$errors          = [];
$success         = '';
$passwordErrors  = [];
$passwordSuccess = '';
$uploadError     = '';
$uploadSuccess   = '';

// Fetches the current CV data for this user
$stmt = $pdo->prepare("SELECT * FROM cvs WHERE id = ?");
$stmt->execute([$userId]);
$cv = $stmt->fetch();

// Checks if any form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['form_type'])) {

    // Validates the CSRF token before processing anything
    if (!isset($_POST['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("Invalid CSRF token. Please go back and try again.");
    }

    // -------------------------------------------------------
    // Handles the CV details update form
    // -------------------------------------------------------
    if ($_POST['form_type'] === 'update_cv') {

        $name             = trim($_POST['name']);
        $keyprogramming   = trim($_POST['keyprogramming']);
        $skills           = trim($_POST['skills']);
        $profile          = trim($_POST['profile']);
        $education        = trim($_POST['education']);
        $work_experience  = trim($_POST['work_experience']);
        $URLlinks         = trim($_POST['URLlinks']);

        // Validates required fields
        if (empty($name))           $errors[] = "Name is required.";
        if (empty($keyprogramming)) $errors[] = "Key programming language is required.";
        if (empty($profile))        $errors[] = "Profile summary is required.";
        if (empty($education))      $errors[] = "Education is required.";

        if (empty($errors)) {

            // Updates the CV row for this user only
            $stmt = $pdo->prepare("UPDATE cvs
                SET name = ?, keyprogramming = ?, skills = ?, profile = ?,
                    education = ?, work_experience = ?, URLlinks = ?
                WHERE id = ?");

            $stmt->execute([
                $name, $keyprogramming, $skills, $profile,
                $education, $work_experience, $URLlinks, $userId
            ]);

            $_SESSION['user_name'] = $name;
            $success = "Your CV has been updated successfully!";

            // Re-fetches updated data
            $stmt = $pdo->prepare("SELECT * FROM cvs WHERE id = ?");
            $stmt->execute([$userId]);
            $cv = $stmt->fetch();
        }
    }

    // -------------------------------------------------------
    // Handles the profile picture upload form
    // -------------------------------------------------------
    if ($_POST['form_type'] === 'upload_picture') {

        // Checks a file was actually selected
        if (!isset($_FILES['profile_picture']) || 
            $_FILES['profile_picture']['error'] === UPLOAD_ERR_NO_FILE) {
            $uploadError = "Please select a picture to upload.";

        } else {
            $file     = $_FILES['profile_picture'];
            $fileName = $file['name'];
            $fileTmp  = $file['tmp_name'];
            $fileSize = $file['size'];
            $fileExt  = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            // Only allows image file types for security
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

            if (!in_array($fileExt, $allowedExtensions)) {
                $uploadError = "Only JPG, PNG, GIF and WEBP images are allowed.";

            // Limits file size to 2MB (2 * 1024 * 1024 = 2097152 bytes)
            } elseif ($fileSize > 2097152) {
                $uploadError = "File size must be under 2MB.";

            } else {

                // Creates a unique filename using the user id and timestamp
                // This prevents filename conflicts if two users upload same filename
                $newFileName = 'profile_' . $userId . '_' . time() . '.' . $fileExt;

                // Creates the uploads folder if it does not already exist
                // 0755 sets the folder permissions
                $uploadDir = 'uploads/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $uploadPath = $uploadDir . $newFileName;

                // Moves the file from the temporary upload location to our folder
                if (move_uploaded_file($fileTmp, $uploadPath)) {

                    // Deletes the old profile picture if one exists
                    // to avoid leaving unused files on the server
                    if (!empty($cv['profile_picture']) && 
                        file_exists($uploadDir . $cv['profile_picture'])) {
                        unlink($uploadDir . $cv['profile_picture']);
                    }

                    // Saves just the filename to the database
                    $stmt = $pdo->prepare("UPDATE cvs SET profile_picture = ? WHERE id = ?");
                    $stmt->execute([$newFileName, $userId]);

                    $uploadSuccess = "Profile picture updated successfully!";

                    // Re-fetches to show new picture
                    $stmt = $pdo->prepare("SELECT * FROM cvs WHERE id = ?");
                    $stmt->execute([$userId]);
                    $cv = $stmt->fetch();

                } else {
                    $uploadError = "Upload failed. Please try again.";
                }
            }
        }
    }

    // -------------------------------------------------------
    // Handles the change password form
    // -------------------------------------------------------
    if ($_POST['form_type'] === 'change_password') {

        $currentPassword = trim($_POST['current_password']);
        $newPassword     = trim($_POST['new_password']);
        $confirmPassword = trim($_POST['confirm_password']);

        if (empty($currentPassword)) {
            $passwordErrors[] = "Please enter your current password.";
        }
        if (empty($newPassword)) {
            $passwordErrors[] = "Please enter a new password.";
        } elseif (strlen($newPassword) < 8) {
            $passwordErrors[] = "New password must be at least 8 characters.";
        } elseif (!preg_match('/[A-Z]/', $newPassword)) {
            $passwordErrors[] = "New password must contain at least one uppercase letter.";
        } elseif (!preg_match('/[0-9]/', $newPassword)) {
            $passwordErrors[] = "New password must contain at least one number.";
        } elseif (!preg_match('/[\W_]/', $newPassword)) {
            $passwordErrors[] = "New password must contain at least one special character.";
        }

        if ($newPassword !== $confirmPassword) {
            $passwordErrors[] = "New passwords do not match.";
        }

        if (empty($passwordErrors)) {

            $stmt = $pdo->prepare("SELECT password FROM cvs WHERE id = ?");
            $stmt->execute([$userId]);
            $row = $stmt->fetch();

            if (!password_verify($currentPassword, $row['password'])) {
                $passwordErrors[] = "Your current password is incorrect.";
            } elseif (password_verify($newPassword, $row['password'])) {
                $passwordErrors[] = "Your new password cannot be the same as your current password.";
            } else {
                $newHashed = password_hash($newPassword, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE cvs SET password = ? WHERE id = ?");
                $stmt->execute([$newHashed, $userId]);
                $passwordSuccess = "Your password has been changed successfully!";
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
    <title>Update CV - AstonCV</title>
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
                    <li><span style="color: var(--primary-color); font-weight: 600;">
                        <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                    </span></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </div>
</header>

<section class="hero">
    <div class="container">
        <div class="hero-content">
            <h1>Update Your CV</h1>
            <p class="subtitle">Keep your profile up to date</p>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">

        <!-- ================================================ -->
        <!-- PROFILE PICTURE FORM -->
        <!-- ================================================ -->
        <div class="form-card" style="margin-bottom: 3rem;">

            <h2 style="margin-bottom: 2rem; color: var(--text-dark); font-size: 1.5rem;">
                Profile Picture
            </h2>

            <!-- Shows current profile picture if one exists -->
            <div style="text-align: center; margin-bottom: 2rem;">
                <?php if (!empty($cv['profile_picture'])): ?>
                    <!-- Displays the uploaded picture as a circle -->
                    <img src="uploads/<?php echo htmlspecialchars($cv['profile_picture']); ?>"
                         alt="Profile picture"
                         style="width: 150px; height: 150px; border-radius: 50%;
                                object-fit: cover; border: 4px solid var(--primary-color);
                                box-shadow: var(--shadow-lg);">
                <?php else: ?>
                    <!-- Shows a placeholder circle if no picture uploaded yet -->
                    <div style="width: 150px; height: 150px; border-radius: 50%;
                                background: var(--bg-light); border: 4px solid var(--border-color);
                                display: flex; align-items: center; justify-content: center;
                                margin: 0 auto; color: var(--text-light); font-size: 3rem;">
                        👤
                    </div>
                <?php endif; ?>
            </div>

            <?php if ($uploadSuccess): ?>
                <div class="alert-success"><?php echo htmlspecialchars($uploadSuccess); ?></div>
            <?php endif; ?>

            <?php if ($uploadError): ?>
                <div class="alert-error"><?php echo htmlspecialchars($uploadError); ?></div>
            <?php endif; ?>

            <!-- File upload form - enctype is required for file uploads -->
            <!-- Without enctype="multipart/form-data" the file will not be sent -->
            <form method="POST" action="update.php" enctype="multipart/form-data">
                <input type="hidden" name="form_type" value="upload_picture">
                <input type="hidden" name="csrf_token"
                       value="<?php echo $_SESSION['csrf_token']; ?>">

                <div class="form-group">
                    <label for="profile_picture">Upload New Picture</label>
                    <!-- accept attribute restricts the file picker to image files only -->
                    <input type="file" id="profile_picture" name="profile_picture"
                           accept="image/jpeg,image/png,image/gif,image/webp"
                           style="padding: 0.5rem;">
                    <span class="field-hint">JPG, PNG, GIF or WEBP. Maximum 2MB.</span>
                </div>

                <button type="submit" class="submit-button">Upload Picture</button>
            </form>
        </div>

        <!-- ================================================ -->
        <!-- CV DETAILS FORM -->
        <!-- ================================================ -->
        <div class="form-card" style="margin-bottom: 3rem;">

            <h2 style="margin-bottom: 2rem; color: var(--text-dark); font-size: 1.5rem;">
                CV Details
            </h2>

            <?php if ($success): ?>
                <div class="alert-success"><?php echo htmlspecialchars($success); ?></div>
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

            <form method="POST" action="update.php">
                <input type="hidden" name="form_type" value="update_cv">
                <input type="hidden" name="csrf_token"
                       value="<?php echo $_SESSION['csrf_token']; ?>">

                <!-- Name field -->
                <div class="form-group">
                    <label for="name">Full Name <span class="required">*</span></label>
                    <input type="text" id="name" name="name"
                           value="<?php echo htmlspecialchars($cv['name']); ?>">
                </div>

                <!-- Email - read only -->
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email"
                           value="<?php echo htmlspecialchars($cv['email']); ?>"
                           disabled style="background: var(--bg-light); cursor: not-allowed;">
                    <span class="field-hint">Email cannot be changed</span>
                </div>

                <!-- Key programming language -->
                <div class="form-group">
                    <label for="keyprogramming">Key Programming Language <span class="required">*</span></label>
                    <input type="text" id="keyprogramming" name="keyprogramming"
                           value="<?php echo htmlspecialchars($cv['keyprogramming']); ?>"
                           placeholder="e.g. Python">
                </div>

                <!-- Skills - multiple languages and technologies -->
                <div class="form-group">
                    <label for="skills">Skills & Technologies
                        <span style="color: var(--text-light); font-weight: 400;">(optional)</span>
                    </label>
                    <input type="text" id="skills" name="skills"
                           value="<?php echo htmlspecialchars($cv['skills'] ?? ''); ?>"
                           placeholder="e.g. Python, JavaScript, C++, MySQL, HTML, CSS">
                    <span class="field-hint">Separate each skill with a comma</span>
                </div>

                <!-- Profile summary -->
                <div class="form-group">
                    <label for="profile">Profile Summary <span class="required">*</span></label>
                    <textarea id="profile" name="profile"><?php echo htmlspecialchars($cv['profile']); ?></textarea>
                </div>

                <!-- Education -->
                <div class="form-group">
                    <label for="education">Education <span class="required">*</span></label>
                    <textarea id="education" name="education"><?php echo htmlspecialchars($cv['education']); ?></textarea>
                </div>

                <!-- Work experience - new field -->
                <div class="form-group">
                    <label for="work_experience">Work Experience
                        <span style="color: var(--text-light); font-weight: 400;">(optional)</span>
                    </label>
                    <textarea id="work_experience" name="work_experience"
                              placeholder="e.g. Software Intern at XYZ Ltd (2024) - worked on..."><?php echo htmlspecialchars($cv['work_experience'] ?? ''); ?></textarea>
                </div>

                <!-- Links -->
                <div class="form-group">
                    <label for="URLlinks">Links
                        <span style="color: var(--text-light); font-weight: 400;">(optional)</span>
                    </label>
                    <input type="text" id="URLlinks" name="URLlinks"
                           value="<?php echo htmlspecialchars($cv['URLlinks'] ?? ''); ?>"
                           placeholder="e.g. https://github.com/yourusername">
                </div>

                <button type="submit" class="submit-button">Save Changes</button>
            </form>
        </div>

        <!-- ================================================ -->
        <!-- CHANGE PASSWORD FORM -->
        <!-- ================================================ -->
        <div class="form-card">

            <h2 style="margin-bottom: 2rem; color: var(--text-dark); font-size: 1.5rem;">
                Change Password
            </h2>

            <?php if ($passwordSuccess): ?>
                <div class="alert-success"><?php echo htmlspecialchars($passwordSuccess); ?></div>
            <?php endif; ?>

            <?php if (!empty($passwordErrors)): ?>
                <div class="alert-error">
                    <ul>
                        <?php foreach ($passwordErrors as $err): ?>
                            <li><?php echo htmlspecialchars($err); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" action="update.php">
                <input type="hidden" name="form_type" value="change_password">
                <input type="hidden" name="csrf_token"
                       value="<?php echo $_SESSION['csrf_token']; ?>">

                <!-- Current password -->
                <div class="form-group">
                    <label for="current_password">Current Password <span class="required">*</span></label>
                    <div class="password-wrapper">
                        <input type="password" id="current_password" name="current_password"
                               placeholder="Enter your current password">
                        <button type="button" class="toggle-password"
                                onclick="togglePassword('current_password', 'eyeIcon1')"
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

                <!-- New password with strength checker -->
                <div class="form-group">
                    <label for="new_password">New Password <span class="required">*</span></label>
                    <div class="password-wrapper">
                        <input type="password" id="new_password" name="new_password"
                               placeholder="Min 8 chars, uppercase, number, special char"
                               oninput="checkPasswordStrength(this.value)">
                        <button type="button" class="toggle-password"
                                onclick="togglePassword('new_password', 'eyeIcon2')"
                                aria-label="Show or hide password">
                            <svg id="eyeIcon2" xmlns="http://www.w3.org/2000/svg"
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

                <!-- Confirm new password with match indicator -->
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password <span class="required">*</span></label>
                    <div class="password-wrapper">
                        <input type="password" id="confirm_password" name="confirm_password"
                               placeholder="Repeat your new password"
                               oninput="checkPasswordMatch()">
                        <button type="button" class="toggle-password"
                                onclick="togglePassword('confirm_password', 'eyeIcon3')"
                                aria-label="Show or hide password">
                            <svg id="eyeIcon3" xmlns="http://www.w3.org/2000/svg"
                                 width="20" height="20" viewBox="0 0 24 24"
                                 fill="none" stroke="currentColor"
                                 stroke-width="2" stroke-linecap="round"
                                 stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                        </button>
                    </div>
                    <small class="password-match" id="matchText"></small>
                </div>

                <button type="submit" class="submit-button">Change Password</button>
            </form>
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

/*
 * checkPasswordStrength()
 * Runs every time the user types in the new password field
 * Checks each rule and updates the checklist, strength bar and label
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
        { width: '0%',   bg: '',         label: '',       color: '' },
        { width: '25%',  bg: '#dc2626',  label: 'Weak',   color: '#dc2626' },
        { width: '50%',  bg: '#f59e0b',  label: 'Fair',   color: '#f59e0b' },
        { width: '75%',  bg: '#3b82f6',  label: 'Good',   color: '#3b82f6' },
        { width: '100%', bg: '#059669',  label: 'Strong', color: '#059669' },
    ];

    fill.style.width      = levels[score].width;
    fill.style.background = levels[score].bg;
    text.textContent      = levels[score].label;
    text.style.color      = levels[score].color;

    checkPasswordMatch();
}

/*
 * checkPasswordMatch()
 * Shows a live green tick or red cross below the confirm field
 */
function checkPasswordMatch() {
    const newPass     = document.getElementById('new_password').value;
    const confirmPass = document.getElementById('confirm_password').value;
    const matchText   = document.getElementById('matchText');

    if (confirmPass.length === 0) {
        matchText.textContent = '';
        return;
    }
    if (newPass === confirmPass) {
        matchText.textContent = '✓ Passwords match';
        matchText.style.color = '#059669';
    } else {
        matchText.textContent = '✗ Passwords do not match';
        matchText.style.color = '#dc2626';
    }
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