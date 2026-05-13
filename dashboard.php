<?php
/*
 * dashboard.php
 * Personal Dashboard - AstonCV
 * Student: Isaac Adjei (240191278)
 *
 * I show the logged-in user their CV preview and quick actions.
 * I fetch their full CV data from the database to display here.
 * I only allow access to logged-in users - redirect to login otherwise.
 * I wrap all database calls in try/catch for proper error handling.
 */

require 'db.php';
session_start();

// I redirect to login if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];

try {
    // I fetch the full CV row for the logged-in user
    $stmt = $pdo->prepare("SELECT * FROM cvs WHERE id = ?");
    $stmt->execute([$userId]);
    $cv = $stmt->fetch();

    // I count how many CVs are in the database total
    $totalCVs = (int) $pdo->query("SELECT COUNT(*) FROM cvs")->fetchColumn();

} catch (PDOException $e) {
    // I redirect home if the database call fails
    header('Location: index.php');
    exit;
}

// I get the first letter of the name for the avatar
$initials = strtoupper(mb_substr(trim($cv['name']), 0, 1));

// I split the skills string into an array for displaying as badges
$skillsArray = [];
if (!empty($cv['skills'])) {
    $skillsArray = array_filter(array_map('trim', explode(',', $cv['skills'])));
}

// I truncate the profile summary for the preview card
$profilePreview = !empty($cv['profile'])
    ? (strlen($cv['profile']) > 200 ? substr($cv['profile'], 0, 200) . '...' : $cv['profile'])
    : 'No profile summary added yet.';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - AstonCV</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* I add page-specific styles for the dashboard layout */

        .dash-layout {
            display: grid;
            grid-template-columns: 260px 1fr;
            gap: 2rem;
            align-items: start;
        }

        /* Sticky sidebar */
        .dash-sidebar {
            position: sticky;
            top: 90px;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .dash-profile-card {
            background: var(--bg-white);
            border-radius: 10px;
            padding: 1.75rem;
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-sm);
            text-align: center;
        }

        .dash-avatar {
            width: 76px;
            height: 76px;
            border-radius: 50%;
            background: var(--primary-faint);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Space Grotesk', sans-serif;
            font-weight: 700;
            font-size: 1.85rem;
            color: var(--primary-color);
            margin: 0 auto 1rem;
            border: 3px solid var(--border-color);
            overflow: hidden;
        }

        .dash-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .dash-user-name {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.05rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 0.2rem;
        }

        .dash-user-email {
            font-size: 0.78rem;
            color: var(--text-light);
            word-break: break-all;
            margin-bottom: 1.25rem;
        }

        /* I style the sidebar nav links */
        .dash-nav-link {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            padding: 0.6rem 0.75rem;
            border-radius: 6px;
            font-size: 0.87rem;
            font-weight: 500;
            color: var(--text-medium);
            text-decoration: none;
            transition: all 0.2s ease;
            margin-bottom: 0.2rem;
        }

        .dash-nav-link:hover,
        .dash-nav-link.active {
            background: var(--primary-faint);
            color: var(--primary-color);
            font-weight: 600;
        }

        .dash-nav-link.danger {
            color: var(--error-color);
        }

        .dash-nav-link.danger:hover {
            background: rgba(220, 38, 38, 0.06);
            color: var(--error-color);
        }

        /* I style each main content card */
        .dash-card {
            background: var(--bg-white);
            border-radius: 10px;
            padding: 1.75rem;
            border: 1px solid var(--border-color);
            border-left: 4px solid var(--primary-color);
            box-shadow: var(--shadow-sm);
            margin-bottom: 1.5rem;
            transition: box-shadow 0.3s ease;
        }

        .dash-card:hover {
            box-shadow: var(--shadow-md);
        }

        .dash-card-title {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            color: var(--primary-color);
            margin-bottom: 1rem;
            padding-bottom: 0.6rem;
            border-bottom: 1px solid var(--border-color);
        }

        /* I lay out the stats row at the top of the dashboard */
        .dash-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .dash-stat-card {
            background: var(--bg-white);
            border-radius: 10px;
            padding: 1.25rem;
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-sm);
            text-align: center;
        }

        .dash-stat-number {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-color);
            line-height: 1;
            margin-bottom: 0.3rem;
        }

        .dash-stat-label {
            font-size: 0.75rem;
            color: var(--text-light);
            text-transform: uppercase;
            letter-spacing: 0.1em;
            font-weight: 500;
        }

        /* CV preview section inside the dashboard */
        .cv-preview-row {
            display: flex;
            align-items: flex-start;
            gap: 1.25rem;
        }

        .cv-preview-avatar {
            width: 52px;
            height: 52px;
            border-radius: 50%;
            background: var(--primary-faint);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Space Grotesk', sans-serif;
            font-weight: 700;
            font-size: 1.2rem;
            color: var(--primary-color);
            flex-shrink: 0;
            border: 2px solid var(--border-color);
            overflow: hidden;
        }

        .cv-preview-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .cv-preview-info {
            flex: 1;
        }

        .cv-preview-name {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 0.2rem;
        }

        .cv-preview-role {
            font-size: 0.85rem;
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 0.6rem;
        }

        .cv-preview-summary {
            font-size: 0.88rem;
            color: var(--text-medium);
            line-height: 1.65;
        }

        .dash-action-row {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
            margin-top: 1.25rem;
        }

        .dash-action-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.65rem 1.25rem;
            border-radius: 6px;
            font-family: 'Space Grotesk', sans-serif;
            font-weight: 600;
            font-size: 0.85rem;
            text-decoration: none;
            transition: all 0.25s ease;
            border: 2px solid;
        }

        .dash-action-btn.primary {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }

        .dash-action-btn.primary:hover {
            background: var(--primary-dark);
            border-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .dash-action-btn.outline {
            background: transparent;
            color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .dash-action-btn.outline:hover {
            background: var(--primary-faint);
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            .dash-layout {
                grid-template-columns: 1fr;
            }
            .dash-sidebar {
                position: static;
            }
            .dash-stats {
                grid-template-columns: 1fr 1fr;
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
                    <a href="dashboard.php" class="active" style="color: var(--accent-bright); font-weight: 600;">
                        <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                    </a>
                </li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </div>
</header>

<!-- ============================================================
     BANNER — Lake campus image with welcome message
     ============================================================ -->
<div style="background:
        linear-gradient(160deg, rgba(26,10,46,0.80) 0%, rgba(92,45,130,0.70) 100%),
        url('images/campus-lake.jpg') center / cover no-repeat;
    padding: 2.5rem 0;">
    <div style="max-width: 1200px; width: 90%; margin: 0 auto; padding: 0 1rem;
                display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
        <div>
            <p style="color: rgba(255,255,255,0.6); font-size: 0.82rem; margin-bottom: 0.35rem;">
                <a href="index.php" style="color: rgba(255,255,255,0.5); text-decoration: none;">AstonCV</a>
                &rsaquo; Dashboard
            </p>
            <h1 style="color: white; font-size: 1.75rem; letter-spacing: -0.02em; margin: 0;">
                Welcome back, <?php echo htmlspecialchars(explode(' ', $cv['name'])[0]); ?>
            </h1>
        </div>
        <a href="cv.php?id=<?php echo $userId; ?>"
           style="display: inline-flex; align-items: center; gap: 0.5rem;
                  background: rgba(255,255,255,0.12); color: white; border: 2px solid rgba(255,255,255,0.35);
                  padding: 0.65rem 1.25rem; border-radius: 6px; text-decoration: none;
                  font-family: 'Space Grotesk', sans-serif; font-weight: 600; font-size: 0.88rem;
                  transition: all 0.25s ease; backdrop-filter: blur(4px);"
           onmouseover="this.style.background='rgba(255,255,255,0.22)'"
           onmouseout="this.style.background='rgba(255,255,255,0.12)'">
            &#128065; View My CV
        </a>
    </div>
</div>

<!-- ============================================================
     MAIN CONTENT
     ============================================================ -->
<section class="section">
    <div class="container">
        <div class="dash-layout">

            <!-- ---- SIDEBAR ---- -->
            <div class="dash-sidebar">
                <div class="dash-profile-card">
                    <div class="dash-avatar">
                        <?php if (!empty($cv['profile_picture'])): ?>
                            <img src="uploads/<?php echo htmlspecialchars($cv['profile_picture']); ?>"
                                 alt="Profile picture">
                        <?php else: ?>
                            <?php echo $initials; ?>
                        <?php endif; ?>
                    </div>
                    <div class="dash-user-name"><?php echo htmlspecialchars($cv['name']); ?></div>
                    <div class="dash-user-email"><?php echo htmlspecialchars($cv['email']); ?></div>

                    <a href="cv.php?id=<?php echo $userId; ?>" class="dash-nav-link active">
                        &#128065; View My CV
                    </a>
                    <a href="update.php" class="dash-nav-link">
                        &#9998; Edit CV
                    </a>
                    <a href="index.php" class="dash-nav-link">
                        &#8592; Browse CVs
                    </a>
                    <a href="logout.php" class="dash-nav-link danger">
                        &#8594; Logout
                    </a>
                </div>
            </div>

            <!-- ---- MAIN CONTENT ---- -->
            <div>

                <!-- Stats row -->
                <div class="dash-stats">
                    <div class="dash-stat-card">
                        <div class="dash-stat-number"><?php echo (int)$cv['view_count']; ?></div>
                        <div class="dash-stat-label">CV Views</div>
                    </div>
                    <div class="dash-stat-card">
                        <div class="dash-stat-number"><?php echo count($skillsArray); ?></div>
                        <div class="dash-stat-label">Skills Listed</div>
                    </div>
                    <div class="dash-stat-card">
                        <div class="dash-stat-number"><?php echo $totalCVs; ?></div>
                        <div class="dash-stat-label">Total CVs</div>
                    </div>
                </div>

                <!-- CV Preview card -->
                <div class="dash-card">
                    <div class="dash-card-title">My CV Preview</div>

                    <div class="cv-preview-row">
                        <div class="cv-preview-avatar">
                            <?php if (!empty($cv['profile_picture'])): ?>
                                <img src="uploads/<?php echo htmlspecialchars($cv['profile_picture']); ?>"
                                     alt="Profile picture">
                            <?php else: ?>
                                <?php echo $initials; ?>
                            <?php endif; ?>
                        </div>
                        <div class="cv-preview-info">
                            <div class="cv-preview-name"><?php echo htmlspecialchars($cv['name']); ?></div>
                            <div class="cv-preview-role">
                                <?php echo htmlspecialchars($cv['keyprogramming']); ?> Developer
                            </div>
                            <p class="cv-preview-summary">
                                <?php echo htmlspecialchars($profilePreview); ?>
                            </p>
                        </div>
                    </div>

                    <!-- Skills badges -->
                    <?php if (!empty($skillsArray)): ?>
                        <div class="skills-container" style="margin-top: 1.25rem;">
                            <?php foreach (array_slice($skillsArray, 0, 8) as $skill): ?>
                                <span class="skill-badge">
                                    <?php echo htmlspecialchars($skill); ?>
                                </span>
                            <?php endforeach; ?>
                            <?php if (count($skillsArray) > 8): ?>
                                <span class="skill-badge" style="background: var(--border-color); color: var(--text-light);">
                                    +<?php echo count($skillsArray) - 8; ?> more
                                </span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <div class="dash-action-row">
                        <a href="cv.php?id=<?php echo $userId; ?>" class="dash-action-btn primary">
                            &#128065; View Full CV
                        </a>
                        <a href="update.php" class="dash-action-btn outline">
                            &#9998; Edit CV
                        </a>
                    </div>
                </div>

                <!-- CV completeness card -->
                <?php
                // I check which sections are filled in to show a completeness score
                $sections = [
                    'Profile Summary' => !empty($cv['profile']),
                    'Education'       => !empty($cv['education']),
                    'Skills'          => !empty($cv['skills']),
                    'Work Experience' => !empty($cv['work_experience']),
                    'Links'           => !empty($cv['URLlinks']),
                    'Profile Picture' => !empty($cv['profile_picture']),
                ];
                $completed = count(array_filter($sections));
                $total     = count($sections);
                $percent   = round(($completed / $total) * 100);
                ?>
                <div class="dash-card">
                    <div class="dash-card-title">CV Completeness</div>

                    <!-- Progress bar -->
                    <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                        <div style="flex: 1; height: 8px; background: var(--border-color);
                                    border-radius: 4px; overflow: hidden;">
                            <div style="width: <?php echo $percent; ?>%; height: 100%;
                                        background: var(--primary-color); border-radius: 4px;
                                        transition: width 1s ease;"></div>
                        </div>
                        <span style="font-family: 'Space Grotesk', sans-serif; font-weight: 700;
                                     color: var(--primary-color); font-size: 0.95rem; white-space: nowrap;">
                            <?php echo $percent; ?>%
                        </span>
                    </div>

                    <!-- Section checklist -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem;">
                        <?php foreach ($sections as $label => $done): ?>
                            <div style="display: flex; align-items: center; gap: 0.5rem;
                                        font-size: 0.85rem;
                                        color: <?php echo $done ? 'var(--success-color)' : 'var(--text-light)'; ?>;">
                                <?php echo $done ? '✓' : '○'; ?>
                                <?php echo $label; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <?php if ($percent < 100): ?>
                        <div class="dash-action-row">
                            <a href="update.php" class="dash-action-btn outline" style="font-size: 0.82rem;">
                                Complete your CV
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Quick links card -->
                <div class="dash-card">
                    <div class="dash-card-title">Quick Actions</div>
                    <div class="dash-action-row">
                        <a href="update.php#picture" class="dash-action-btn outline">
                            &#128247; Update Photo
                        </a>
                        <a href="update.php#password" class="dash-action-btn outline">
                            &#128274; Change Password
                        </a>
                        <a href="index.php" class="dash-action-btn outline">
                            &#128196; Browse All CVs
                        </a>
                    </div>
                </div>

            </div><!-- end main -->
        </div><!-- end dash-layout -->
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
                <li><a href="cv.php?id=<?php echo $userId; ?>">My CV</a></li>
                <li><a href="update.php">Edit CV</a></li>
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
// I add the scrolled class to the navbar when the user scrolls
const header = document.getElementById('main-header');
window.addEventListener('scroll', function () {
    header.classList.toggle('scrolled', window.scrollY > 30);
});
</script>

</body>
</html>
