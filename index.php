<?php
/*
 * index.php
 * CV Browse and Search Page - AstonCV
 * Student: Isaac Adjei (240191278)
 *
 * I display all CVs as cards in a responsive grid.
 * I support searching by name or programming language via GET.
 * I fetch the total CV count for the stats bar on the hero.
 * I sanitise all output with htmlspecialchars to prevent XSS.
 * I wrap the database call in try/catch for proper error handling.
 */

require 'db.php';
session_start();

// I get the search term from the URL if one was submitted
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// I use try/catch to handle any database errors gracefully
try {
    // I fetch total CV count for the stats bar — always the full count
    $countStmt = $pdo->query("SELECT COUNT(*) FROM cvs");
    $totalCVs  = (int) $countStmt->fetchColumn();

    // I build the main query depending on whether a search term exists
    if ($search !== '') {
        $stmt = $pdo->prepare(
            "SELECT id, name, email, keyprogramming, profile_picture, view_count
             FROM cvs
             WHERE name LIKE ? OR keyprogramming LIKE ?
             ORDER BY name ASC"
        );
        $stmt->execute(['%' . $search . '%', '%' . $search . '%']);
    } else {
        $stmt = $pdo->prepare(
            "SELECT id, name, email, keyprogramming, profile_picture, view_count
             FROM cvs
             ORDER BY name ASC"
        );
        $stmt->execute();
    }

    $cvs = $stmt->fetchAll();

} catch (PDOException $e) {
    // I show a friendly error rather than crashing the page
    $cvs      = [];
    $totalCVs = 0;
    $dbError  = "Database error — please try again later.";
}

// I collect unique programming languages for the live filter dropdown
$languages = array_unique(array_column($cvs, 'keyprogramming'));
sort($languages);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AstonCV - Browse CVs</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<!-- ============================================================
     PRELOADER — I show this briefly while the page loads,
     then JS hides it by adding the .hidden class.
     ============================================================ -->
<div id="preloader">
    <div class="preloader-spinner"></div>
    <span class="preloader-text">AstonCV</span>
</div>

<!-- ============================================================
     HEADER — Dark purple navbar, flush against the hero.
     Logo now correctly points to images/logo.svg
     ============================================================ -->
<header id="main-header">
    <div class="header-content">
        <a href="index.php" style="text-decoration: none; display: flex; align-items: center;">
            <img src="images/logo.svg" alt="Aston University" class="logo">
        </a>
        <nav>
            <ul>
                <li><a href="index.php" class="active">Browse CVs</a></li>
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
     HERO — Campus photo with purple gradient overlay.
     I add .hero--image so the CSS applies the background photo.
     ============================================================ -->
<section class="hero hero--image">
    <div class="hero-content">
        <h1>
            <span class="hero-highlight">Aston</span>CV
        </h1>
        <p class="subtitle">
            Browse and search CVs from Aston University programmers.
            Find talent, get inspired, showcase your own skills.
        </p>
        <div style="display: flex; gap: 1rem; flex-wrap: wrap; margin-top: 0.5rem;">
            <a href="#cvs" class="cta-button">Browse CVs</a>
            <?php if (!isset($_SESSION['user_id'])): ?>
                <a href="register.php" class="cta-button" style="background: transparent; border-color: rgba(255,255,255,0.5); color: white;">
                    Add Your CV
                </a>
            <?php endif; ?>
        </div>

        <!-- Stats bar — animated counters showing live numbers -->
        <div class="stats-bar">
            <div class="stat-item">
                <span class="stat-number" data-target="<?php echo $totalCVs; ?>">0</span>
                <span class="stat-label">CVs Listed</span>
            </div>
            <div class="stat-item">
                <span class="stat-number" data-target="2026" data-nofmt="true">2026</span>
                <span class="stat-label">Year</span>
            </div>
        </div>
    </div>
</section>

<!-- ============================================================
     MARQUEE — Pure CSS infinite scroll strip.
     I duplicate the content so the loop is seamless.
     ============================================================ -->
<div class="marquee-strip" aria-hidden="true">
    <div class="marquee-track">
        <!-- I repeat the items twice so the animation loops seamlessly -->
        <span class="marquee-item"><span class="marquee-dot"></span>Aston University</span>
        <span class="marquee-item"><span class="marquee-dot"></span>CV Database</span>
        <span class="marquee-item"><span class="marquee-dot"></span>Open to Recruiters</span>
        <span class="marquee-item"><span class="marquee-dot"></span>Showcase Your Skills</span>
        <span class="marquee-item"><span class="marquee-dot"></span>Birmingham</span>
        <span class="marquee-item"><span class="marquee-dot"></span>EECS 2026</span>
        <span class="marquee-item"><span class="marquee-dot"></span>Student Talent</span>
        <span class="marquee-item"><span class="marquee-dot"></span>Find Developers</span>
        <!-- I copy the same items again for the seamless loop -->
        <span class="marquee-item"><span class="marquee-dot"></span>Aston University</span>
        <span class="marquee-item"><span class="marquee-dot"></span>CV Database</span>
        <span class="marquee-item"><span class="marquee-dot"></span>Open to Recruiters</span>
        <span class="marquee-item"><span class="marquee-dot"></span>Showcase Your Skills</span>
        <span class="marquee-item"><span class="marquee-dot"></span>Birmingham</span>
        <span class="marquee-item"><span class="marquee-dot"></span>EECS 2026</span>
        <span class="marquee-item"><span class="marquee-dot"></span>Student Talent</span>
        <span class="marquee-item"><span class="marquee-dot"></span>Find Developers</span>
    </div>
</div>

<!-- ============================================================
     MAIN CONTENT — Search, filter, sort, and CV grid
     ============================================================ -->
<section class="section" id="cvs">
    <div class="container">

        <?php if (isset($dbError)): ?>
            <div class="alert-error"><?php echo htmlspecialchars($dbError); ?></div>
        <?php endif; ?>

        <h2><?php echo $search ? 'Results for "' . htmlspecialchars($search) . '"' : 'All CVs'; ?></h2>
        <p class="section-intro">
            <?php echo $totalCVs; ?> CV<?php echo $totalCVs !== 1 ? 's' : ''; ?> from Aston University students and programmers.
        </p>

        <!-- Search form — submits via GET so results are shareable -->
        <form method="GET" action="index.php" class="search-form">
            <input
                type="text"
                name="search"
                id="searchInput"
                placeholder="Search by name or programming language..."
                value="<?php echo htmlspecialchars($search); ?>"
                class="search-input"
                autocomplete="off">
            <button type="submit" class="search-button">Search</button>
            <?php if ($search): ?>
                <a href="index.php" class="cta-button-secondary" style="padding: 0.85rem 1.25rem; text-decoration: none; display: inline-flex; align-items: center; font-size: 0.9rem;">
                    Clear
                </a>
            <?php endif; ?>
        </form>

        <!-- Filter and Sort bar — I handle these with JavaScript so no page reload -->
        <div class="filter-bar">
            <select class="filter-select" id="langFilter">
                <option value="">All Languages</option>
                <?php foreach ($languages as $lang): ?>
                    <option value="<?php echo htmlspecialchars($lang); ?>">
                        <?php echo htmlspecialchars($lang); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select class="filter-select" id="sortSelect">
                <option value="name">Sort: A → Z</option>
                <option value="name-desc">Sort: Z → A</option>
                <option value="views">Sort: Most Viewed</option>
            </select>

            <!-- I update this count live via JS as filters change -->
            <span class="filter-count" id="visibleCount">
                <?php echo count($cvs); ?> CV<?php echo count($cvs) !== 1 ? 's' : ''; ?>
            </span>
        </div>

        <!-- CV cards grid — each card has data attributes for JS filtering -->
        <div class="cv-grid" id="cvGrid">
            <?php if (empty($cvs) && !isset($dbError)): ?>
                <div class="no-results">
                    <strong>No CVs found</strong>
                    <?php if ($search): ?>
                        No results for "<?php echo htmlspecialchars($search); ?>". Try a different search.
                    <?php else: ?>
                        No CVs have been added yet.
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php foreach ($cvs as $cv):
                // I get the first letter of the name for the avatar initials
                $initials = strtoupper(mb_substr(trim($cv['name']), 0, 1));
            ?>
                <div class="cv-card"
                     data-name="<?php echo htmlspecialchars(strtolower($cv['name'])); ?>"
                     data-lang="<?php echo htmlspecialchars($cv['keyprogramming']); ?>"
                     data-views="<?php echo (int)$cv['view_count']; ?>">

                    <!-- Card header: avatar + name + email -->
                    <div class="cv-card-header">
                        <div class="cv-avatar">
                            <?php if (!empty($cv['profile_picture'])): ?>
                                <!-- I show the uploaded profile photo if they have one -->
                                <img src="uploads/<?php echo htmlspecialchars($cv['profile_picture']); ?>"
                                     alt="<?php echo htmlspecialchars($cv['name']); ?>">
                            <?php else: ?>
                                <!-- I show the first letter of their name as initials -->
                                <?php echo $initials; ?>
                            <?php endif; ?>
                        </div>
                        <div class="cv-card-meta">
                            <h3><?php echo htmlspecialchars($cv['name']); ?></h3>
                            <span class="cv-email"><?php echo htmlspecialchars($cv['email']); ?></span>
                        </div>
                    </div>

                    <!-- Card body: language badge + view count -->
                    <div class="cv-card-body">
                        <div class="skills-container">
                            <span class="skill-badge">
                                <?php echo htmlspecialchars($cv['keyprogramming']); ?>
                            </span>
                        </div>
                        <p style="color: var(--text-light); font-size: 0.82rem; display: flex; align-items: center; gap: 0.35rem;">
                            <!-- Eye icon using Unicode -->
                            &#128065;
                            <?php echo (int)$cv['view_count']; ?> view<?php echo $cv['view_count'] != 1 ? 's' : ''; ?>
                        </p>
                    </div>

                    <!-- Card footer: View CV button -->
                    <div class="cv-card-footer">
                        <a href="cv.php?id=<?php echo $cv['id']; ?>" class="cta-button">
                            View CV
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- I show this message when JS filters produce no results -->
        <div class="no-results" id="noFilterResults" style="display: none;">
            <strong>No CVs match this filter</strong>
            Try selecting a different language or sorting option.
        </div>

    </div>
</section>

<!-- ============================================================
     FOOTER — Real content with links and student info
     ============================================================ -->
<!-- ============================================================
     CONTACT SECTION — Simple enquiry form for visitors
     ============================================================ -->
<section class="section" style="background: var(--bg-white); border-top: 1px solid var(--border-color);">
    <div class="container">
        <h2>Get in Touch</h2>
        <p class="section-intro">Have a question or want to get involved? Send a message or email directly at <a href="mailto:contact@isaacadjei.me" style="color: var(--primary-color); font-weight: 600;">contact@isaacadjei.me</a></p>

        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'sent'): ?>
            <div class="alert-success">Message sent - we will get back to you soon.</div>
        <?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'error'): ?>
            <div class="alert-error">Please fill in all fields with a valid email address.</div>
        <?php endif; ?>

        <form action="contact_handler.php" method="POST" class="form-card" style="max-width: 600px; margin: 0; padding: 2rem; box-shadow: none; border: 1px solid var(--border-color);">
            <!-- I include a CSRF token to protect against cross-site request forgery -->
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? bin2hex(random_bytes(32))); ?>">
            <!--
                HONEYPOT FIELD — I hide this from real users with CSS.
                Bots fill in every field automatically, so if this has
                a value the handler knows it is a bot and blocks it silently.
            -->
            <input type="text" name="website" value="" style="display:none;" tabindex="-1" autocomplete="off" aria-hidden="true">
            <div class="form-group">
                <label for="contact_name">Your Name <span class="required">*</span></label>
                <input type="text" id="contact_name" name="contact_name" placeholder="e.g. Isaac Adjei" required>
            </div>
            <div class="form-group">
                <label for="contact_email">Email Address <span class="required">*</span></label>
                <input type="email" id="contact_email" name="contact_email" placeholder="e.g. yourname@aston.ac.uk" required>
            </div>
            <div class="form-group">
                <label for="contact_message">Message <span class="required">*</span></label>
                <textarea id="contact_message" name="contact_message" placeholder="Write your message here..." required></textarea>
            </div>
            <button type="submit" class="submit-button">Send Message</button>
        </form>
    </div>
</section>

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
                    <li><a href="update.php">Update CV</a></li>
                    <li><a href="logout.php">Logout</a></li>
                <?php endif; ?>
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

<!-- ============================================================
     JAVASCRIPT
     I handle three things here:
     1. Preloader — fade out after page loads
     2. Navbar scroll class — adds .scrolled when user scrolls down
     3. Scroll reveal — animates cards in as they enter the viewport
     4. Stats counters — animates numbers up from 0
     5. Live filter — filters cards by language without page reload
     6. Live sort — reorders cards without page reload
     ============================================================ -->
<script>
// ---- 1. PRELOADER ----
// I wait for the page to fully load then fade out the preloader
window.addEventListener('load', function () {
    const preloader = document.getElementById('preloader');
    if (preloader) {
        preloader.classList.add('hidden');
    }
});

// ---- 2. NAVBAR SCROLL CLASS ----
// I add .scrolled to the header when the user scrolls down
// so the CSS can make it more opaque with a blur effect
const header = document.getElementById('main-header');
window.addEventListener('scroll', function () {
    if (window.scrollY > 30) {
        header.classList.add('scrolled');
    } else {
        header.classList.remove('scrolled');
    }
});

// ---- 3. SCROLL REVEAL ----
// I use IntersectionObserver to detect when each card enters the screen.
// When it does, I add the .revealed class which triggers the CSS animation.
const revealObserver = new IntersectionObserver(function (entries) {
    entries.forEach(function (entry) {
        if (entry.isIntersecting) {
            entry.target.classList.add('revealed');
            // I stop watching the card once it has appeared
            revealObserver.unobserve(entry.target);
        }
    });
}, {
    threshold: 0.1,      // I trigger when 10% of the card is visible
    rootMargin: '0px 0px -40px 0px'
});

// I watch every CV card on the page
document.querySelectorAll('.cv-card').forEach(function (card) {
    revealObserver.observe(card);
});

// ---- 4. STATS COUNTERS ----
// I animate each stat number from 0 up to its target value.
// The data-target attribute on each .stat-number holds the final value.
function animateCounter(el) {
    const target  = parseInt(el.getAttribute('data-target'), 10);
    const duration = 1200; // I count up over 1.2 seconds
    const step    = target / (duration / 16); // I update every ~16ms (60fps)
    let current   = 0;

    const timer = setInterval(function () {
        current += step;
        if (current >= target) {
            current = target;
            clearInterval(timer);
        }
        // I check if this stat should skip number formatting (e.g. the year)
        if (el.getAttribute('data-nofmt') === 'true') {
            el.textContent = Math.floor(current);
        } else {
            el.textContent = Math.floor(current).toLocaleString();
        }
    }, 16);
}

// I only start the counter animation when the stats bar is visible
const statsObserver = new IntersectionObserver(function (entries) {
    entries.forEach(function (entry) {
        if (entry.isIntersecting) {
            document.querySelectorAll('.stat-number').forEach(animateCounter);
            statsObserver.disconnect(); // I only run once
        }
    });
}, { threshold: 0.3 });

const statsBar = document.querySelector('.stats-bar');
if (statsBar) statsObserver.observe(statsBar);

// ---- 5 & 6. LIVE FILTER AND SORT ----
// I filter and sort the CV cards in real time without a page reload.
// I read the selected language and sort option, then show/hide/reorder cards.

const langFilter   = document.getElementById('langFilter');
const sortSelect   = document.getElementById('sortSelect');
const cvGrid       = document.getElementById('cvGrid');
const visibleCount = document.getElementById('visibleCount');
const noResults    = document.getElementById('noFilterResults');

function applyFiltersAndSort() {
    const selectedLang = langFilter.value.toLowerCase();
    const sortBy       = sortSelect.value;

    // I get all the cards as an array so I can sort them
    const cards = Array.from(cvGrid.querySelectorAll('.cv-card'));

    // I sort the array first
    cards.sort(function (a, b) {
        if (sortBy === 'name') {
            // I sort alphabetically A to Z by name
            return a.getAttribute('data-name').localeCompare(b.getAttribute('data-name'));
        } else if (sortBy === 'name-desc') {
            // I sort alphabetically Z to A
            return b.getAttribute('data-name').localeCompare(a.getAttribute('data-name'));
        } else if (sortBy === 'views') {
            // I sort by view count, highest first
            return parseInt(b.getAttribute('data-views'), 10) - parseInt(a.getAttribute('data-views'), 10);
        }
        return 0;
    });

    // I re-append the cards in sorted order
    cards.forEach(function (card) {
        cvGrid.appendChild(card);
    });

    // I now show or hide cards based on the language filter
    let shown = 0;
    cards.forEach(function (card) {
        const cardLang = card.getAttribute('data-lang').toLowerCase();
        const matches  = selectedLang === '' || cardLang === selectedLang;

        if (matches) {
            card.style.display = '';  // I show the card
            shown++;
        } else {
            card.style.display = 'none';  // I hide the card
        }
    });

    // I update the count label
    visibleCount.textContent = shown + ' CV' + (shown !== 1 ? 's' : '');

    // I show the no-results message if everything is hidden
    if (shown === 0) {
        noResults.style.display = 'block';
    } else {
        noResults.style.display = 'none';
    }
}

// I listen for changes on both dropdowns
if (langFilter) langFilter.addEventListener('change', applyFiltersAndSort);
if (sortSelect) sortSelect.addEventListener('change', applyFiltersAndSort);
</script>

</body>
</html>