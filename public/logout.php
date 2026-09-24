<?php
/*
 * logout: destroys the session and returns to the homepage.
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