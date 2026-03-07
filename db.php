<?php
/*
 * db.php
 * Database Connection File - AstonCV
 * Student: Isaac Adjei (240191278)
 *
 * This file creates a connection to the MySQL database using PDO.
 * It is included at the top of every page that needs database access.
 * I load credentials from config.php so that sensitive details are
 * never hardcoded here and never accidentally pushed to GitHub.
 *
 * Using PDO (PHP Data Objects) is more secure than older MySQL methods
 * because it supports prepared statements which prevent SQL injection.
 */

// Load my database credentials from config.php
// config.php is excluded from GitHub via .gitignore
require_once 'config.php';

try {
    // Creates a new PDO connection using the credentials from config.php
    // charset=utf8 ensures special characters are handled correctly
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);

    // Tells PDO to throw exceptions when errors occur
    // This makes debugging much easier if something goes wrong
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    // If the connection fails, stop the page and show the error
    // This prevents the rest of the page running without a database
    die("Connection failed: " . $e->getMessage());
}
?>