<?php
/*
 * contact form handler: post-only, validates every field and drops honeypot hits silently.
 * sanitises all output and sends the message with mail().
 */

session_start();

// only allow POST requests - reject anything else immediately
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// honeypot: the hidden "website" field is empty for humans. a value means a bot, so
// redirect as if it worked.
if (!empty($_POST['website'])) {
    header('Location: index.php?msg=sent#contact');
    exit;
}

// sanitise all inputs before doing anything with them
$name    = htmlspecialchars(trim($_POST['contact_name']    ?? ''));
$email   = htmlspecialchars(trim($_POST['contact_email']   ?? ''));
$message = htmlspecialchars(trim($_POST['contact_message'] ?? ''));

// check all required fields are filled in
if (empty($name) || empty($email) || empty($message)) {
    header('Location: index.php?msg=error#contact');
    exit;
}

// validate the email address format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: index.php?msg=error#contact');
    exit;
}

// build the email
$to      = 'contact@isaacadjei.me';
$subject = 'AstonCV Enquiry from ' . $name;
$body    = "Name: $name\nEmail: $email\n\nMessage:\n$message";
$headers = "From: noreply@isaacadjei.me\r\nReply-To: $email\r\n";

// attempt to send - only works on the live server, not localhost
mail($to, $subject, $body, $headers);

header('Location: index.php?msg=sent#contact');
exit;