<?php
/*
 * cv.php
 * CV Detail Page - AstonCV
 * Student: Isaac Adjei (240191278)
 *
 * Displays the full details of a single CV.
 * The CV is identified by the id passed in the URL e.g. cv.php?id=1
 * Shows profile picture, skills, work experience and links.
 * Increments the view count every time someone visits this page.
 * Shows logged-in user's name and logout in the nav when authenticated.
 */

// Loads the database connection
require 'db.php';

// Starts the session so we can check if the user is logged in
session_start();

// Checks that an id was actually provided in the URL
if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$id = $_GET['id'];

// Uses a prepared statement to fetch the CV with that id
$stmt = $pdo->prepare("SELECT * FROM cvs WHERE id = ?");
$stmt->execute([$id]);
$cv = $stmt->fetch();

// If no CV was found with that id, redirects back to homepage
if (!$cv) {
    header('Location: index.php');
    exit;
}

// Increments the view count by 1 every time this page is loaded
// This tracks how popular each CV is
$stmt = $pdo->prepare("UPDATE cvs SET view_count = view_count + 1 WHERE id = ?");
$stmt->execute([$id]);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($cv['name']); ?> - AstonCV</title>
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
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li>
                            <a href="update.php" style="color: var(--primary-color); font-weight: 600;">
                                <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                            </a>
                        </li>
                        <li><a href="logout.php">Logout</a></li>
                    <?php else: ?>
                        <li><a href="register.php">Register</a></li>
                        <li><a href="login.php">Login</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </div>
</header>

<!-- Hero banner showing the person's name and key language -->
<section class="hero">
    <div class="container">
        <div class="hero-content">

            <!-- Shows profile picture in the hero if one has been uploaded -->
            <?php if (!empty($cv['profile_picture'])): ?>
                <img src="uploads/<?php echo htmlspecialchars($cv['profile_picture']); ?>"
                     alt="<?php echo htmlspecialchars($cv['name']); ?>"
                     style="width: 120px; height: 120px; border-radius: 50%;
                            object-fit: cover; border: 4px solid white;
                            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
                            margin-bottom: 1.5rem;">
            <?php endif; ?>

            <h1><?php echo htmlspecialchars($cv['name']); ?></h1>
            <p class="subtitle"><?php echo htmlspecialchars($cv['keyprogramming']); ?> Developer</p>

            <!-- Shows view count below the name -->
            <p style="color: rgba(255,255,255,0.6); font-size: 0.85rem; margin-top: 0.5rem;">
                👁 <?php echo (int)$cv['view_count']; ?> view<?php echo $cv['view_count'] != 1 ? 's' : ''; ?>
            </p>

        </div>
    </div>
</section>

<!-- Full CV details section -->
<section class="section">
    <div class="container">
        <div class="cv-detail-card">

            <!-- Contact information -->
            <div class="cv-detail-section">
                <h2>Contact</h2>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($cv['email']); ?></p>
            </div>

            <!-- Profile summary -->
            <div class="cv-detail-section">
                <h2>Profile</h2>
                <p><?php echo htmlspecialchars($cv['profile']); ?></p>
            </div>

            <!-- Education background -->
            <div class="cv-detail-section">
                <h2>Education</h2>
                <p><?php echo htmlspecialchars($cv['education']); ?></p>
            </div>

            <!-- Work experience - only shows if the user has added some -->
            <?php if (!empty($cv['work_experience'])): ?>
                <div class="cv-detail-section">
                    <h2>Work Experience</h2>
                    <p><?php echo nl2br(htmlspecialchars($cv['work_experience'])); ?></p>
                </div>
            <?php endif; ?>

            <!-- Skills section - displays each skill as a pill badge -->
            <?php if (!empty($cv['skills'])): ?>
                <div class="cv-detail-section">
                    <h2>Skills & Technologies</h2>
                    <div class="skills-container">
                        <?php
                        // Splits the skills string by comma into an array
                        // e.g. "Python, JavaScript, C++" becomes ['Python', 'JavaScript', 'C++']
                        $skillsArray = explode(',', $cv['skills']);
                        foreach ($skillsArray as $skill):
                            // trim() removes any extra spaces around each skill
                            $skill = trim($skill);
                            if (!empty($skill)):
                        ?>
                            <!-- Each skill displayed as a coloured pill -->
                            <span class="skill-badge">
                                <?php echo htmlspecialchars($skill); ?>
                            </span>
                        <?php
                            endif;
                        endforeach;
                        ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Key programming language -->
            <div class="cv-detail-section">
                <h2>Key Programming Language</h2>
                <p><?php echo htmlspecialchars($cv['keyprogramming']); ?></p>
            </div>

            <!-- Links - only shows if one was provided -->
            <?php if (!empty($cv['URLlinks'])): ?>
                <div class="cv-detail-section">
                    <h2>Links</h2>
                    <p>
                        <a href="<?php echo htmlspecialchars($cv['URLlinks']); ?>"
                           target="_blank"
                           style="color: var(--primary-color); font-weight: 500;">
                            <?php echo htmlspecialchars($cv['URLlinks']); ?>
                        </a>
                    </p>
                </div>
            <?php endif; ?>

            <!-- Back button and Download PDF button -->
            <div style="display: flex; gap: 1rem; margin-top: 2rem; flex-wrap: wrap;">
                <a href="index.php" class="cta-button">
                    &larr; Back to All CVs
                </a>
                <a href="export_cv.php?id=<?php echo $cv['id']; ?>" 
                   class="cta-button" 
                   style="background: #28a745;">
                    &#8595; Download CV as PDF
                </a>
            </div>

        </div>
    </div>
</section>

<footer>
    <div class="container">
        <p>&copy; 2026 AstonCV</p>
    </div>
</footer>

</body>
</html>