<?php
/*
 * logout.php
 * Logout - AstonCV
 * Student: Isaac Adjei (240191278)
 *
 * Destroys the session to log the user out.
 * Redirects to the homepage after logging out.
 */

// Starts the session so we can destroy it
session_start();

// Removes all session variables
session_unset();

// Destroys the session completely
session_destroy();

// Redirects to the homepage
header('Location: index.php');
exit;
?>