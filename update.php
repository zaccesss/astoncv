<?php
/*
 * update.php
 * Update CV Page - AstonCV
 * Student: Isaac Adjei (240191278)
 *
 * I allow logged-in users to update their CV details.
 * I allow password changes with strength enforcement.
 * I allow profile picture uploads with file type and size validation.
 * I only allow access to logged-in users - redirect to login otherwise.
 * I use a CSRF token to prevent cross-site request forgery.
 * I wrap all database calls in try/catch for proper error handling.
 */

require 'db.php';
session_start();

// I redirect to login if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// I generate a CSRF token if one does not already exist
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

// I fetch the current CV data for this user
try {
    $stmt = $pdo->prepare("SELECT * FROM cvs WHERE id = ?");
    $stmt->execute([$userId]);
    $cv = $stmt->fetch();
} catch (PDOException $e) {
    header('Location: login.php');
    exit;
}

// I process whichever form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['form_type'])) {

    // I validate the CSRF token before processing anything
    if (!isset($_POST['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("Invalid CSRF token. Please go back and try again.");
    }

    // --------------------------------------------------------
    // CV DETAILS UPDATE
    // --------------------------------------------------------
    if ($_POST['form_type'] === 'update_cv') {

        $name            = trim($_POST['name']            ?? '');
        $keyprogramming  = trim($_POST['keyprogramming']  ?? '');
        $skills          = trim($_POST['skills']          ?? '');
        $profile         = trim($_POST['profile']         ?? '');
        $education       = trim($_POST['education']       ?? '');
        $work_experience = trim($_POST['work_experience'] ?? '');
        $URLlinks        = trim($_POST['URLlinks']        ?? '');

        if (empty($name))           $errors[] = "Name is required.";
        if (empty($keyprogramming)) $errors[] = "Key programming language is required.";
        if (empty($profile))        $errors[] = "Profile summary is required.";
        if (empty($education))      $errors[] = "Education is required.";

        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare(
                    "UPDATE cvs SET name = ?, keyprogramming = ?, skills = ?,
                     profile = ?, education = ?, work_experience = ?, URLlinks = ?
                     WHERE id = ?"
                );
                $stmt->execute([
                    $name, $keyprogramming, $skills, $profile,
                    $education, $work_experience, $URLlinks, $userId
                ]);
                $_SESSION['user_name'] = $name;
                $success = "Your CV has been updated successfully!";

                // I re-fetch to show the updated values
                $stmt = $pdo->prepare("SELECT * FROM cvs WHERE id = ?");
                $stmt->execute([$userId]);
                $cv = $stmt->fetch();

            } catch (PDOException $e) {
                $errors[] = "Something went wrong. Please try again.";
            }
        }
    }

    // --------------------------------------------------------
    // PROFILE PICTURE UPLOAD
    // --------------------------------------------------------
    if ($_POST['form_type'] === 'upload_picture') {

        if (!isset($_FILES['profile_picture']) ||
            $_FILES['profile_picture']['error'] === UPLOAD_ERR_NO_FILE) {
            $uploadError = "Please select a picture to upload.";
        } else {
            $file    = $_FILES['profile_picture'];
            $fileTmp = $file['tmp_name'];
            $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

            // I only allow safe image formats
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

            if (!in_array($fileExt, $allowed)) {
                $uploadError = "Only JPG, PNG, GIF and WEBP images are allowed.";
            } elseif ($file['size'] > 2097152) {
                // I limit file size to 2MB
                $uploadError = "File size must be under 2MB.";
            } else {
                // I create a unique filename to avoid conflicts
                $newFileName = 'profile_' . $userId . '_' . time() . '.' . $fileExt;
                $uploadDir   = 'uploads/';

                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                if (move_uploaded_file($fileTmp, $uploadDir . $newFileName)) {
                    try {
                        // I delete the old profile picture to keep the server tidy
                        if (!empty($cv['profile_picture']) &&
                            file_exists($uploadDir . $cv['profile_picture'])) {
                            unlink($uploadDir . $cv['profile_picture']);
                        }

                        $pdo->prepare("UPDATE cvs SET profile_picture = ? WHERE id = ?")
                            ->execute([$newFileName, $userId]);

                        $uploadSuccess = "Profile picture updated!";

                        $stmt = $pdo->prepare("SELECT * FROM cvs WHERE id = ?");
                        $stmt->execute([$userId]);
                        $cv = $stmt->fetch();

                    } catch (PDOException $e) {
                        $uploadError = "Database error. Please try again.";
                    }
                } else {
                    $uploadError = "Upload failed. Please try again.";
                }
            }
        }
    }

    // --------------------------------------------------------
    // CHANGE PASSWORD
    // --------------------------------------------------------
    if ($_POST['form_type'] === 'change_password') {

        $currentPassword = trim($_POST['current_password'] ?? '');
        $newPassword     = trim($_POST['new_password']     ?? '');
        $confirmPassword = trim($_POST['confirm_password'] ?? '');

        if (empty($currentPassword)) $passwordErrors[] = "Please enter your current password.";
        if (empty($newPassword))     $passwordErrors[] = "Please enter a new password.";
        elseif (strlen($newPassword) < 8)              $passwordErrors[] = "New password must be at least 8 characters.";
        elseif (!preg_match('/[A-Z]/', $newPassword))  $passwordErrors[] = "New password must contain at least one uppercase letter.";
        elseif (!preg_match('/[0-9]/', $newPassword))  $passwordErrors[] = "New password must contain at least one number.";
        elseif (!preg_match('/[\W_]/', $newPassword))  $passwordErrors[] = "New password must contain at least one special character.";

        if ($newPassword !== $confirmPassword) {
            $passwordErrors[] = "New passwords do not match.";
        }

        if (empty($passwordErrors)) {
            try {
                $stmt = $pdo->prepare("SELECT password FROM cvs WHERE id = ?");
                $stmt->execute([$userId]);
                $row = $stmt->fetch();

                if (!password_verify($currentPassword, $row['password'])) {
                    $passwordErrors[] = "Your current password is incorrect.";
                } elseif (password_verify($newPassword, $row['password'])) {
                    $passwordErrors[] = "Your new password cannot be the same as your current password.";
                } else {
                    $pdo->prepare("UPDATE cvs SET password = ? WHERE id = ?")
                        ->execute([password_hash($newPassword, PASSWORD_DEFAULT), $userId]);
                    $passwordSuccess = "Password changed successfully!";
                }
            } catch (PDOException $e) {
                $passwordErrors[] = "Something went wrong. Please try again.";
            }
        }
    }
}

// I get the first letter of the name for the avatar
$initials = strtoupper(mb_substr(trim($cv['name']), 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update CV - AstonCV</title>
    <link rel="icon" type="image/svg+xml" href="images/logo.svg">
    <link rel="stylesheet" href="style.css">
    <style>
        /* I lay out the update page as a two-column grid like the dashboard */
        .update-layout {
            display: grid;
            grid-template-columns: 260px 1fr;
            gap: 2rem;
            align-items: start;
        }

        /* Sticky sidebar with user info */
        .update-sidebar {
            position: sticky;
            top: 90px;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .update-user-card {
            background: var(--bg-white);
            border-radius: 10px;
            padding: 1.75rem;
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-sm);
            text-align: center;
        }

        .update-avatar {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            background: var(--primary-faint);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Space Grotesk', sans-serif;
            font-weight: 700;
            font-size: 1.75rem;
            color: var(--primary-color);
            margin: 0 auto 0.85rem;
            border: 3px solid var(--border-color);
            overflow: hidden;
        }

        .update-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .update-user-name {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 0.2rem;
        }

        .update-user-email {
            font-size: 0.78rem;
            color: var(--text-light);
            word-break: break-all;
            margin-bottom: 1.25rem;
        }

        .update-nav-link {
            display: block;
            padding: 0.55rem 0.75rem;
            border-radius: 6px;
            font-size: 0.87rem;
            font-weight: 500;
            color: var(--text-medium);
            text-decoration: none;
            transition: all 0.2s ease;
            margin-bottom: 0.25rem;
            text-align: left;
        }

        .update-nav-link:hover,
        .update-nav-link.active {
            background: var(--primary-faint);
            color: var(--primary-color);
            font-weight: 600;
        }

        /* I style each form section as a card */
        .update-section {
            background: var(--bg-white);
            border-radius: 10px;
            padding: 2rem;
            border: 1px solid var(--border-color);
            border-left: 4px solid var(--primary-color);
            box-shadow: var(--shadow-sm);
            margin-bottom: 1.75rem;
        }

        .update-section-title {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 1.25rem;
            padding-bottom: 0.85rem;
            border-bottom: 1px solid var(--border-color);
            letter-spacing: -0.01em;
        }

        /* Password strength bar */
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

        .password-rules {
            list-style: none;
            padding: 0;
            margin: 0.6rem 0 0;
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .password-rules li {
            font-size: 0.8rem;
            color: var(--text-light);
            transition: color 0.2s ease;
        }

        .password-match {
            font-size: 0.8rem;
            margin-top: 0.3rem;
            display: block;
            font-weight: 600;
        }

        @media (max-width: 768px) {
            .update-layout {
                grid-template-columns: 1fr;
            }
            .update-sidebar {
                position: static;
            }
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
                <li>
                    <a href="dashboard.php" style="color: var(--accent-bright); font-weight: 600;">
                        <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                    </a>
                </li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </div>
</header>

<!-- ============================================================
     PAGE BANNER — Aerial campus image, slim strip
     ============================================================ -->
<div style="background:
        linear-gradient(160deg, rgba(26,10,46,0.82) 0%, rgba(92,45,130,0.72) 100%),
        url('images/campus-aerial.jpg') center / cover no-repeat;
    padding: 2.5rem 0;">
    <div style="max-width: 1200px; width: 90%; margin: 0 auto; padding: 0 1rem;">
        <p style="color: rgba(255,255,255,0.65); font-size: 0.82rem; margin-bottom: 0.4rem;">
            <a href="index.php" style="color: rgba(255,255,255,0.55); text-decoration: none;">Browse CVs</a>
            &rsaquo;
            <a href="dashboard.php" style="color: rgba(255,255,255,0.55); text-decoration: none;">Dashboard</a>
            &rsaquo;
            <span style="color: white;">Update CV</span>
        </p>
        <h1 style="color: white; font-size: 1.75rem; letter-spacing: -0.02em; margin: 0;">
            Update Your CV
        </h1>
    </div>
</div>

<!-- ============================================================
     MAIN CONTENT
     ============================================================ -->
<section class="section">
    <div class="container">
        <div class="update-layout">

            <!-- ---- SIDEBAR ---- -->
            <div class="update-sidebar">
                <div class="update-user-card">
                    <div class="update-avatar">
                        <?php if (!empty($cv['profile_picture'])): ?>
                            <img src="uploads/<?php echo htmlspecialchars($cv['profile_picture']); ?>"
                                 alt="Profile picture">
                        <?php else: ?>
                            <?php echo $initials; ?>
                        <?php endif; ?>
                    </div>
                    <div class="update-user-name"><?php echo htmlspecialchars($cv['name']); ?></div>
                    <div class="update-user-email"><?php echo htmlspecialchars($cv['email']); ?></div>

                    <a href="cv.php?id=<?php echo $userId; ?>" class="update-nav-link">
                        &#128065; View My CV
                    </a>
                    <a href="dashboard.php" class="update-nav-link">
                        &#9783; Dashboard
                    </a>
                    <a href="index.php" class="update-nav-link">
                        &#8592; Browse CVs
                    </a>
                    <a href="logout.php" class="update-nav-link" style="color: var(--error-color);">
                        &#8594; Logout
                    </a>
                </div>
            </div>

            <!-- ---- MAIN FORMS ---- -->
            <div>

                <!-- ============================================
                     PROFILE PICTURE
                     ============================================ -->
                <div class="update-section" id="picture">
                    <div class="update-section-title">Profile Picture</div>

                    <?php if ($uploadSuccess): ?>
                        <div class="alert-success"><?php echo htmlspecialchars($uploadSuccess); ?></div>
                    <?php endif; ?>
                    <?php if ($uploadError): ?>
                        <div class="alert-error"><?php echo htmlspecialchars($uploadError); ?></div>
                    <?php endif; ?>

                    <!-- Current picture preview -->
                    <div style="display: flex; align-items: center; gap: 1.5rem; margin-bottom: 1.5rem; flex-wrap: wrap;">
                        <div style="width: 72px; height: 72px; border-radius: 50%;
                                    background: var(--primary-faint); border: 3px solid var(--border-color);
                                    display: flex; align-items: center; justify-content: center;
                                    font-family: 'Space Grotesk', sans-serif; font-weight: 700;
                                    font-size: 1.75rem; color: var(--primary-color); overflow: hidden; flex-shrink: 0;">
                            <?php if (!empty($cv['profile_picture'])): ?>
                                <img src="uploads/<?php echo htmlspecialchars($cv['profile_picture']); ?>"
                                     alt="Current picture"
                                     style="width: 100%; height: 100%; object-fit: cover;">
                            <?php else: ?>
                                <?php echo $initials; ?>
                            <?php endif; ?>
                        </div>
                        <div>
                            <p style="font-size: 0.88rem; color: var(--text-medium); margin: 0;">
                                <?php echo !empty($cv['profile_picture']) ? 'Profile picture uploaded.' : 'No picture uploaded yet. Your initial is shown instead.'; ?>
                            </p>
                            <p style="font-size: 0.8rem; color: var(--text-light); margin-top: 0.25rem;">
                                JPG, PNG, GIF or WEBP - max 2MB
                            </p>
                        </div>
                    </div>

                    <!-- enctype="multipart/form-data" is required for file uploads -->
                    <form method="POST" action="update.php" enctype="multipart/form-data">
                        <input type="hidden" name="form_type" value="upload_picture">
                        <input type="hidden" name="csrf_token"
                               value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                        <div class="form-group">
                            <label for="profile_picture">Choose New Picture</label>
                            <input type="file" id="profile_picture" name="profile_picture"
                                   accept="image/jpeg,image/png,image/gif,image/webp"
                                   style="padding: 0.5rem;">
                        </div>
                        <button type="submit" class="submit-button" style="max-width: 200px;">
                            Upload Picture
                        </button>
                    </form>
                </div>

                <!-- ============================================
                     CV DETAILS
                     ============================================ -->
                <div class="update-section" id="cv-details">
                    <div class="update-section-title">CV Details</div>

                    <?php if ($success): ?>
                        <div class="alert-success"><?php echo htmlspecialchars($success); ?></div>
                    <?php endif; ?>
                    <?php if (!empty($errors)): ?>
                        <div class="alert-error">
                            <ul>
                                <?php foreach ($errors as $err): ?>
                                    <li><?php echo htmlspecialchars($err); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="update.php">
                        <input type="hidden" name="form_type" value="update_cv">
                        <input type="hidden" name="csrf_token"
                               value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

                        <div class="form-group">
                            <label for="name">Full Name <span class="required">*</span></label>
                            <input type="text" id="name" name="name"
                                   value="<?php echo htmlspecialchars($cv['name']); ?>">
                        </div>

                        <!-- Email is read-only — cannot be changed after registration -->
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email"
                                   value="<?php echo htmlspecialchars($cv['email']); ?>"
                                   disabled
                                   style="background: var(--bg-light); cursor: not-allowed; opacity: 0.7;">
                            <span class="field-hint">Email cannot be changed after registration</span>
                        </div>

                        <div class="form-group">
                            <label for="keyprogramming">Key Programming Language <span class="required">*</span></label>
                            <input type="text" id="keyprogramming" name="keyprogramming"
                                   value="<?php echo htmlspecialchars($cv['keyprogramming']); ?>"
                                   placeholder="e.g. Python">
                        </div>

                        <div class="form-group">
                            <label for="skills">
                                Skills &amp; Technologies
                                <span style="color: var(--text-light); font-weight: 400;">(optional)</span>
                            </label>
                            <input type="text" id="skills" name="skills"
                                   value="<?php echo htmlspecialchars($cv['skills'] ?? ''); ?>"
                                   placeholder="e.g. Python, JavaScript, MySQL, HTML, CSS">
                            <span class="field-hint">Separate each skill with a comma</span>
                        </div>

                        <div class="form-group">
                            <label for="profile">Profile Summary <span class="required">*</span></label>
                            <textarea id="profile" name="profile"><?php echo htmlspecialchars($cv['profile']); ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="education">Education <span class="required">*</span></label>
                            <textarea id="education" name="education"><?php echo htmlspecialchars($cv['education']); ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="work_experience">
                                Work Experience
                                <span style="color: var(--text-light); font-weight: 400;">(optional)</span>
                            </label>
                            <textarea id="work_experience" name="work_experience"
                                      placeholder="e.g. Software Intern at XYZ Ltd (2024) - worked on..."><?php echo htmlspecialchars($cv['work_experience'] ?? ''); ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="URLlinks">
                                Links
                                <span style="color: var(--text-light); font-weight: 400;">(optional)</span>
                            </label>
                            <input type="text" id="URLlinks" name="URLlinks"
                                   value="<?php echo htmlspecialchars($cv['URLlinks'] ?? ''); ?>"
                                   placeholder="e.g. https://github.com/alexjohnson, https://linkedin.com/in/alexjohnson">
                        </div>

                        <button type="submit" class="submit-button">Save Changes</button>
                    </form>
                </div>

                <!-- ============================================
                     CHANGE PASSWORD
                     ============================================ -->
                <div class="update-section" id="password">
                    <div class="update-section-title">Change Password</div>

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
                               value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

                        <div class="form-group">
                            <label for="current_password">Current Password <span class="required">*</span></label>
                            <div class="password-wrapper">
                                <input type="password" id="current_password" name="current_password"
                                       placeholder="Enter your current password"
                                       autocomplete="current-password">
                                <button type="button" class="password-toggle"
                                        onclick="togglePassword('current_password', 'eye1')"
                                        aria-label="Show or hide password">
                                    <svg id="eye1" xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                         viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                        <circle cx="12" cy="12" r="3"/>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="new_password">New Password <span class="required">*</span></label>
                            <div class="password-wrapper">
                                <input type="password" id="new_password" name="new_password"
                                       placeholder="Min 8 chars, uppercase, number, special char"
                                       autocomplete="new-password"
                                       oninput="checkPasswordStrength(this.value)">
                                <button type="button" class="password-toggle"
                                        onclick="togglePassword('new_password', 'eye2')"
                                        aria-label="Show or hide password">
                                    <svg id="eye2" xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                         viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                        <circle cx="12" cy="12" r="3"/>
                                    </svg>
                                </button>
                            </div>
                            <div class="password-strength">
                                <div class="strength-fill" id="strengthFill"></div>
                            </div>
                            <span class="strength-text" id="strengthText"></span>
                            <ul class="password-rules">
                                <li id="rule-length">At least 8 characters</li>
                                <li id="rule-upper">At least one uppercase letter</li>
                                <li id="rule-number">At least one number</li>
                                <li id="rule-special">At least one special character (! @ # $ etc.)</li>
                            </ul>
                        </div>

                        <div class="form-group">
                            <label for="confirm_password">Confirm New Password <span class="required">*</span></label>
                            <div class="password-wrapper">
                                <input type="password" id="confirm_password" name="confirm_password"
                                       placeholder="Repeat your new password"
                                       autocomplete="new-password"
                                       oninput="checkPasswordMatch()">
                                <button type="button" class="password-toggle"
                                        onclick="togglePassword('confirm_password', 'eye3')"
                                        aria-label="Show or hide password">
                                    <svg id="eye3" xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                         viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                        <circle cx="12" cy="12" r="3"/>
                                    </svg>
                                </button>
                            </div>
                            <span class="password-match" id="matchText"></span>
                        </div>

                        <button type="submit" class="submit-button">Change Password</button>
                    </form>
                </div>

            </div><!-- end main forms -->
        </div><!-- end update-layout -->
    </div>
</section>

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
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="cv.php?id=<?php echo $userId; ?>">My CV</a></li>
                <li><a href="logout.php">Logout</a></li>
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
// I add scrolled class to navbar on scroll
const header = document.getElementById('main-header');
window.addEventListener('scroll', function () {
    header.classList.toggle('scrolled', window.scrollY > 30);
});

// I toggle password field visibility
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

// I check password strength on every keystroke
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
    checkPasswordMatch();
}

// I show a live tick or cross below the confirm password field
function checkPasswordMatch() {
    const newPass     = document.getElementById('new_password').value;
    const confirmPass = document.getElementById('confirm_password').value;
    const matchText   = document.getElementById('matchText');
    if (!confirmPass.length) { matchText.textContent = ''; return; }
    if (newPass === confirmPass) {
        matchText.textContent = '✓ Passwords match';
        matchText.style.color = '#059669';
    } else {
        matchText.textContent = '✗ Passwords do not match';
        matchText.style.color = '#dc2626';
    }
}

// I update a single password rule item with a tick or cross
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