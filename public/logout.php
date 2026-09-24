<?php
/*
 * logout.php
 * logout - AstonCV
 * author: Isaac Adjei
 *
 * destroys the session to log the user out.
 * redirects to the homepage after logging out.
 */

// starts the session so we can destroy it
session_start();

// removes all session variables
session_unset();

// destroys the session completely
session_destroy();

// redirects to the homepage
header('Location: index.php');
exit;
?>