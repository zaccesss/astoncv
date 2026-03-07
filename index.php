<?php
/*
 * index.php
 * CV Browse and Search Page - AstonCV
 * Student: Isaac Adjei (240191278)
 *
 * Displays all CVs as cards in a grid layout.
 * Supports searching by name or programming language.
 * Shows profile picture on each card if one has been uploaded.
 * Shows logged-in user's name and logout in the nav when authenticated.
 * All output is sanitised with htmlspecialchars to prevent XSS attacks.
 */

// Loads the database connection
require 'db.php';

// Starts the session so we can check if the user is logged in
session_start();

// Checks if the user submitted a search term via the URL
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Builds the query depending on whether a search term was entered
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

<header id="main-header">
    <div class="container">
        <div class="header-content">
            <a href="index.php" style="text-decoration: none;">
                <img src="logo.svg" alt="Aston University" style="height: 50px; width: auto;">
            </a>
            <nav>
                <ul>
                    <li><a href="index.php" class="active">Browse CVs</a></li>
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

<!-- Hero banner -->
<section class="hero">
    <div class="container">
        <div class="hero-content">
            <h1>AstonCV</h1>
            <p class="subtitle">Browse and search CVs from Aston University students</p>
        </div>
    </div>
</section>

<!-- CV list and search section -->
<section class="section">
    <div class="container">

        <h2><?php echo $search ? 'Results for "' . htmlspecialchars($search) . '"' : 'All CVs'; ?></h2>
        <div class="section-divider"></div>

        <!-- Search form -->
        <form method="GET" action="index.php" class="search-form">
            <input
                type="text"
                name="search"
                placeholder="Search by name or programming language..."
                value="<?php echo htmlspecialchars($search); ?>"
                class="search-input">
            <button type="submit" class="search-button">Search</button>
            <?php if ($search): ?>
                <a href="index.php" class="clear-search">Clear</a>
            <?php endif; ?>
        </form>

        <!-- Results count -->
        <p class="results-count">
            <?php echo count($cvs); ?> CV<?php echo count($cvs) !== 1 ? 's' : ''; ?> found
        </p>

        <!-- No results message -->
        <?php if (empty($cvs)): ?>
            <p class="no-results">
                No CVs found matching "<?php echo htmlspecialchars($search); ?>". Try a different search.
            </p>
        <?php endif; ?>

        <!-- CV cards grid -->
        <div class="cv-grid">
            <?php foreach ($cvs as $cv): ?>
                <div class="cv-card">

                    <!-- Profile picture at top of card -->
                    <div style="text-align: center; margin-bottom: 1.2rem;">
                        <?php if (!empty($cv['profile_picture'])): ?>
                            <!-- Shows the uploaded profile picture as a circle -->
                            <img src="uploads/<?php echo htmlspecialchars($cv['profile_picture']); ?>"
                                 alt="<?php echo htmlspecialchars($cv['name']); ?>"
                                 style="width: 80px; height: 80px; border-radius: 50%;
                                        object-fit: cover;
                                        border: 3px solid var(--primary-color);
                                        box-shadow: var(--shadow-md);">
                        <?php else: ?>
                            <!-- Shows a placeholder circle if no picture uploaded -->
                            <div style="width: 80px; height: 80px; border-radius: 50%;
                                        background: var(--bg-light);
                                        border: 3px solid var(--border-color);
                                        display: flex; align-items: center;
                                        justify-content: center;
                                        margin: 0 auto;
                                        font-size: 2rem;">
                                👤
                            </div>
                        <?php endif; ?>
                    </div>

                    <h3><?php echo htmlspecialchars($cv['name']); ?></h3>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($cv['email']); ?></p>
                    <p><strong>Key Language:</strong> <?php echo htmlspecialchars($cv['keyprogramming']); ?></p>

                    <!-- View count shown on the card -->
                    <p style="color: var(--text-light); font-size: 0.85rem; margin-bottom: 1rem;">
                        👁 <?php echo (int)$cv['view_count']; ?> view<?php echo $cv['view_count'] != 1 ? 's' : ''; ?>
                    </p>

                    <a href="cv.php?id=<?php echo $cv['id']; ?>" class="cta-button">View CV</a>
                </div>
            <?php endforeach; ?>
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