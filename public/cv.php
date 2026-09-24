<?php
/*
 * cv detail page for ?id=: counts views and shows Edit only to the owner.
 */

require 'db.php';
session_start();

// check that an ID was actually passed in the URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$id = (int) $_GET['id'];

try {
    // fetch the full CV row for this ID using a prepared statement
    $stmt = $pdo->prepare("SELECT * FROM cvs WHERE id = ?");
    $stmt->execute([$id]);
    $cv = $stmt->fetch();

    // redirect home if no CV exists with this ID
    if (!$cv) {
        header('Location: index.php');
        exit;
    }

    // increment the view count by 1 each time someone views this page
    $pdo->prepare("UPDATE cvs SET view_count = view_count + 1 WHERE id = ?")
        ->execute([$id]);

} catch (PDOException $e) {
    // redirect home if anything goes wrong with the database
    header('Location: index.php');
    exit;
}

// get the first letter of the name for the avatar initials
$initials = strtoupper(mb_substr(trim($cv['name']), 0, 1));

// check if the logged-in user is the owner of this CV
$isOwner = isset($_SESSION['user_id']) && (int)$_SESSION['user_id'] === (int)$cv['id'];

// split the skills string into an array of individual skill badges
$skillsArray = [];
if (!empty($cv['skills'])) {
    $skillsArray = array_filter(array_map('trim', explode(',', $cv['skills'])));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($cv['name']); ?> - AstonCV</title>
    <link rel="icon" type="image/svg+xml" href="images/logo.svg">
    <link rel="stylesheet" href="style.css">
    <style>
        /*
         * add page-specific styles here rather than cluttering style.css.
         * these only apply to the CV detail page.
         */

        /* use a two-column layout: document on left, sidebar on right */
        .cv-page-layout {
            display: grid;
            grid-template-columns: 1fr 320px;
            gap: 2rem;
            max-width: 1100px;
            margin: 0 auto;
            align-items: start;
        }

        /* the main CV document card */
        .cv-doc {
            background: var(--bg-white);
            border-radius: 12px;
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--border-color);
            overflow: hidden;
        }

        /* purple header strip at the top of the document */
        .cv-doc-header {
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-color) 100%);
            padding: 2.5rem;
            display: flex;
            align-items: center;
            gap: 1.75rem;
            color: white;
        }

        /* large avatar in the document header */
        .cv-doc-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: rgba(255,255,255,0.15);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Space Grotesk', sans-serif;
            font-weight: 700;
            font-size: 2rem;
            color: white;
            flex-shrink: 0;
            border: 3px solid rgba(255,255,255,0.4);
            overflow: hidden;
        }

        .cv-doc-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .cv-doc-header-info h1 {
            font-size: 1.75rem;
            color: white;
            margin-bottom: 0.3rem;
            letter-spacing: -0.02em;
        }

        .cv-doc-header-info p {
            color: rgba(255,255,255,0.8);
            font-size: 0.95rem;
        }

        .cv-doc-header-info .cv-role {
            color: rgba(255,255,255,0.95);
            font-weight: 600;
            font-size: 1rem;
            margin-bottom: 0.25rem;
        }

        /* body of the CV document */
        .cv-doc-body {
            padding: 2.5rem;
        }

        /* each section inside the CV */
        .cv-section {
            margin-bottom: 2rem;
            padding-bottom: 2rem;
            border-bottom: 1px solid var(--border-color);
        }

        .cv-section:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        /* small uppercase label like a real CV */
        .cv-section-label {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.15em;
            color: var(--primary-color);
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* draw a thin line after the label to fill the width */
        .cv-section-label::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border-color);
        }

        .cv-section p {
            color: var(--text-medium);
            line-height: 1.8;
            font-size: 0.93rem;
        }

        /* sidebar card */
        .cv-sidebar {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
            position: sticky;
            top: 90px;  /* I stick the sidebar below the navbar */
        }

        .cv-sidebar-card {
            background: var(--bg-white);
            border-radius: 10px;
            padding: 1.5rem;
            border: 1px solid var(--border-color);
            border-left: 4px solid var(--primary-color);
            box-shadow: var(--shadow-sm);
        }

        .cv-sidebar-card h3 {
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.14em;
            color: var(--text-light);
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .cv-sidebar-card p {
            font-size: 0.9rem;
            color: var(--text-medium);
            line-height: 1.6;
            word-break: break-word;
        }

        .cv-sidebar-card a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            word-break: break-all;
        }

        .cv-sidebar-card a:hover {
            text-decoration: underline;
        }

        /* view count badge in sidebar */
        .view-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            background: var(--primary-faint);
            color: var(--primary-color);
            padding: 0.4rem 0.85rem;
            border-radius: 100px;
            font-size: 0.82rem;
            font-weight: 600;
            font-family: 'Space Grotesk', sans-serif;
        }

        /* make the print version clean and readable */
        @media print {
            .cv-sidebar,
            #main-header,
            footer,
            .breadcrumb-bar { display: none !important; }
            .cv-page-layout { grid-template-columns: 1fr; gap: 0; }
            .cv-doc-header {
                background: #5c2d82 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .cv-doc { box-shadow: none; border: none; }
            body { background: white; }
            .section { padding: 0 !important; }
            .container { padding: 0 !important; width: 100% !important; max-width: 100% !important; }
        }

        /* stack to single column on mobile */
        @media (max-width: 768px) {
            .cv-page-layout {
                grid-template-columns: 1fr;
            }

            .cv-sidebar {
                position: static;
            }

            .cv-doc-header {
                flex-direction: column;
                text-align: center;
            }
        }
    </style>
</head>
<body>

<!-- ============================================================
     HEADER - Same dark navbar as every other page
     ============================================================ -->
<header id="main-header">
    <div class="header-content">
        <a href="index.php" style="text-decoration: none; display: flex; align-items: center;">
            <img src="images/logo.svg" alt="Aston University" class="logo">
        </a>
        <nav>
            <ul>
                <li><a href="index.php">Browse CVs</a></li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li>
                        <a href="dashboard.php" style="color: var(--accent-bright); font-weight: 600;">
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
</header>

<!-- ============================================================
     SLIM HERO - No empty purple box. Just a thin breadcrumb
     banner so the page doesn't start abruptly after the navbar.
     ============================================================ -->
<div class="breadcrumb-bar" style="background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-color) 100%);
            padding: 1.25rem 0; border-bottom: 1px solid rgba(255,255,255,0.08);">
    <div style="max-width: 1200px; width: 90%; margin: 0 auto; padding: 0 1rem;">
        <p style="color: rgba(255,255,255,0.7); font-size: 0.85rem; margin: 0;">
            <a href="index.php" style="color: rgba(255,255,255,0.6); text-decoration: none;">Browse CVs</a>
            &rsaquo;
            <span style="color: white;"><?php echo htmlspecialchars($cv['name']); ?></span>
        </p>
    </div>
</div>

<!-- ============================================================
     MAIN CONTENT - Two-column layout: CV doc + sidebar
     ============================================================ -->
<section class="section">
    <div class="container">
        <div class="cv-page-layout">

            <!-- ================================================
                 LEFT: CV DOCUMENT
                 ================================================ -->
            <div class="cv-doc">

                <!-- purple header with avatar and name -->
                <div class="cv-doc-header">
                    <div class="cv-doc-avatar">
                        <?php if (!empty($cv['profile_picture'])): ?>
                            <!-- show the uploaded profile photo -->
                            <img src="uploads/<?php echo htmlspecialchars($cv['profile_picture']); ?>"
                                 alt="<?php echo htmlspecialchars($cv['name']); ?>">
                        <?php else: ?>
                            <!-- show the first letter as initials -->
                            <?php echo $initials; ?>
                        <?php endif; ?>
                    </div>
                    <div class="cv-doc-header-info">
                        <p class="cv-role"><?php echo htmlspecialchars($cv['keyprogramming']); ?> Developer</p>
                        <h1><?php echo htmlspecialchars($cv['name']); ?></h1>
                        <p><?php echo htmlspecialchars($cv['email']); ?></p>
                    </div>
                </div>

                <!-- CV body sections -->
                <div class="cv-doc-body">

                    <!-- profile Summary -->
                    <?php if (!empty($cv['profile'])): ?>
                    <div class="cv-section">
                        <div class="cv-section-label">Profile Summary</div>
                        <p><?php echo nl2br(htmlspecialchars($cv['profile'])); ?></p>
                    </div>
                    <?php endif; ?>

                    <!-- education -->
                    <?php if (!empty($cv['education'])): ?>
                    <div class="cv-section">
                        <div class="cv-section-label">Education</div>
                        <p><?php echo nl2br(htmlspecialchars($cv['education'])); ?></p>
                    </div>
                    <?php endif; ?>

                    <!-- work Experience -->
                    <?php if (!empty($cv['work_experience'])): ?>
                    <div class="cv-section">
                        <div class="cv-section-label">Work Experience</div>
                        <p><?php echo nl2br(htmlspecialchars($cv['work_experience'])); ?></p>
                    </div>
                    <?php endif; ?>

                    <!-- skills -->
                    <?php if (!empty($skillsArray)): ?>
                    <div class="cv-section">
                        <div class="cv-section-label">Skills & Technologies</div>
                        <div class="skills-container" style="margin-top: 0.5rem;">
                            <?php foreach ($skillsArray as $skill): ?>
                                <span class="skill-badge">
                                    <?php echo htmlspecialchars($skill); ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- links - I split by | to support multiple URLs -->
                    <?php if (!empty($cv['URLlinks'])): ?>
                    <div class="cv-section">
                        <div class="cv-section-label">Links</div>
                        <div style="display: flex; flex-direction: column; gap: 0.5rem; margin-top: 0.25rem;">
                            <?php
                            // split the links field by | to get individual URLs
                            $links = array_filter(array_map('trim', explode('|', $cv['URLlinks'])));
                            foreach ($links as $link):
                                // make sure the link starts with http so it works as an href
                                $href = (strpos($link, 'http') === 0) ? $link : 'https://' . $link;
                                // detect the link type to show a helpful label
                                if (strpos($href, 'linkedin') !== false) {
                                    $label = 'LinkedIn';
                                } elseif (strpos($href, 'github') !== false) {
                                    $label = 'GitHub';
                                } else {
                                    $label = $href;
                                }
                            ?>
                                <a href="<?php echo htmlspecialchars($href); ?>"
                                   target="_blank"
                                   rel="noopener noreferrer"
                                   style="color: var(--primary-color); font-weight: 600;
                                          font-size: 0.9rem; text-decoration: none;
                                          display: inline-flex; align-items: center; gap: 0.35rem;">
                                    &#128279; <?php echo htmlspecialchars($label); ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                </div>
            </div>

            <!-- ================================================
                 RIGHT: SIDEBAR
                 ================================================ -->
            <div class="cv-sidebar">

                <!-- view count -->
                <div class="cv-sidebar-card">
                    <h3>Engagement</h3>
                    <span class="view-badge">
                        &#128065;
                        <?php echo (int)$cv['view_count']; ?> view<?php echo $cv['view_count'] != 1 ? 's' : ''; ?>
                    </span>
                </div>

                <!-- key language -->
                <div class="cv-sidebar-card">
                    <h3>Key Language</h3>
                    <span class="skill-badge" style="font-size: 0.9rem; padding: 0.4rem 1rem;">
                        <?php echo htmlspecialchars($cv['keyprogramming']); ?>
                    </span>
                </div>

                <!-- contact -->
                <div class="cv-sidebar-card">
                    <h3>Contact</h3>
                    <p>
                        <a href="mailto:<?php echo htmlspecialchars($cv['email']); ?>">
                            <?php echo htmlspecialchars($cv['email']); ?>
                        </a>
                    </p>
                </div>

                <!-- Actions -->
                <div class="cv-sidebar-card">
                    <h3>Actions</h3>
                    <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                        <a href="index.php" class="cta-button-secondary"
                           style="text-decoration: none; display: inline-flex; align-items: center;
                                  justify-content: center; padding: 0.7rem 1rem; font-size: 0.88rem;
                                  border-radius: 6px; font-family: 'Space Grotesk', sans-serif;
                                  font-weight: 600; border: 2px solid var(--primary-color);
                                  color: var(--primary-color); transition: all 0.3s ease;">
                            &larr; Back to CVs
                        </a>

                        <?php if ($isOwner): ?>
                            <!-- only show Edit to the CV owner -->
                            <a href="update.php" class="cta-button-primary"
                               style="text-decoration: none; display: inline-flex; align-items: center;
                                      justify-content: center; padding: 0.7rem 1rem; font-size: 0.88rem;
                                      border-radius: 6px; font-family: 'Space Grotesk', sans-serif;
                                      font-weight: 600; background: var(--primary-color); color: white;
                                      border: 2px solid var(--primary-color); transition: all 0.3s ease;">
                                &#9998; Edit My CV
                            </a>
                        <?php endif; ?>

                        <!-- download CV as PDF using mPDF export -->
                        <a href="export_cv.php?id=<?php echo $id; ?>"
                           style="text-decoration: none; display: inline-flex; align-items: center;
                                  justify-content: center; gap: 0.4rem; padding: 0.7rem 1rem;
                                  font-size: 0.88rem; border-radius: 6px;
                                  font-family: 'Space Grotesk', sans-serif; font-weight: 600;
                                  background: var(--primary-color); color: white;
                                  border: 2px solid var(--primary-color); transition: all 0.3s ease;"
                           onmouseover="this.style.background='var(--primary-dark)'; this.style.borderColor='var(--primary-dark)'"
                           onmouseout="this.style.background='var(--primary-color)'; this.style.borderColor='var(--primary-color)'">
                            &#11015; Download PDF
                        </a>

                        <!-- print button - triggers browser print dialog -->
                        <button onclick="window.print()"
                                style="padding: 0.7rem 1rem; font-size: 0.88rem;
                                       border-radius: 6px; font-family: 'Space Grotesk', sans-serif;
                                       font-weight: 600; border: 2px solid var(--border-color);
                                       background: white; color: var(--text-medium);
                                       cursor: pointer; transition: all 0.3s ease;"
                                onmouseover="this.style.borderColor='var(--primary-color)'"
                                onmouseout="this.style.borderColor='var(--border-color)'">
                            &#128438; Print CV
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>

<!-- ============================================================
     FOOTER - Same as index.php
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
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="dashboard.php">My Dashboard</a></li>
                    <li><a href="logout.php">Logout</a></li>
                <?php endif; ?>
            </ul>
        </div>
        <div class="footer-col">
            <h4>Project Info</h4>
            <ul>
                <li><a href="https://github.com/zaccesss/astoncv" target="_blank" rel="noopener">GitHub Repository</a></li>
                <li><a href="http://astoncv.zacess.com" target="_blank" rel="noopener">Live Site</a></li>
                <li><a href="https://www.aston.ac.uk" target="_blank" rel="noopener">Aston University</a></li>
            </ul>
        </div>
    </div>
    <div class="footer-bottom">
        <p>&copy; 2026 AstonCV - <a href="https://www.aston.ac.uk" target="_blank" rel="noopener noreferrer">Aston University</a> - Isaac Adjei</p>
    </div>
</footer>

<script>
// add the scrolled class to the navbar when the user scrolls down
const header = document.getElementById('main-header');
window.addEventListener('scroll', function () {
    header.classList.toggle('scrolled', window.scrollY > 30);
});
</script>

</body>
</html>
