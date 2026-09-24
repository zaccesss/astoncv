<?php
/*
 * db.php
 * database Connection File - AstonCV
 * author: Isaac Adjei
 *
 * this file creates a connection to the MySQL database using PDO.
 * it is included at the top of every page that needs database access.
 * load credentials from config.php so that sensitive details are
 * never hardcoded here and never accidentally pushed to GitHub.
 *
 * using PDO (PHP Data Objects) is more secure than older MySQL methods
 * because it supports prepared statements which prevent SQL injection.
 */

// load my database credentials from config.php
// config.php is excluded from GitHub via .gitignore
require_once 'config.php';

try {
    // creates a new PDO connection using the credentials from config.php
    // charset=utf8 ensures special characters are handled correctly
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);

    // tells PDO to throw exceptions when errors occur
    // this makes debugging much easier if something goes wrong
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    // if the connection fails, stop the page and show the error
    // this prevents the rest of the page running without a database
    die("Connection failed: " . $e->getMessage());
}
?>
