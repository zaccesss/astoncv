<?php
/*
 * contact_handler.php
 * Contact Form Handler - AstonCV
 * Student: Isaac Adjei (240191278)
 *
 * I handle the enquiry form submission from index.php.
 * I validate all inputs before doing anything with them.
 * I use a honeypot field to silently block spam bots.
 * I send the message to contact@isaacadjei.me using PHP mail().
 * I redirect back to index.php with a success or error flag.
 *
 * SECURITY MEASURES USED HERE:
 * 1. Honeypot field — bots fill it in, humans leave it empty.
 *    If it has any value I silently reject the submission.
 * 2. POST-only — GET requests are rejected immediately.
 * 3. Input validation — all fields checked before processing.
 * 4. Email validation — filter_var ensures a real email format.
 * 5. htmlspecialchars — all output sanitised to prevent XSS.
 */

session_start();

// I only allow POST requests — reject anything else immediately
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// ----------------------------------------------------------------
// HONEYPOT CHECK
// I include a hidden field called "website" in the contact form.
// Real users never see or fill it in because it is hidden with CSS.
// Bots automatically fill in every field they find, so if this
// field has any value I know it is a bot and silently redirect.
// I pretend it worked so bots don't know they were caught.
// ----------------------------------------------------------------
if (!empty($_POST['website'])) {
    header('Location: index.php?msg=sent#contact');
    exit;
}

// I sanitise all inputs before doing anything with them
$name    = htmlspecialchars(trim($_POST['contact_name']    ?? ''));
$email   = htmlspecialchars(trim($_POST['contact_email']   ?? ''));
$message = htmlspecialchars(trim($_POST['contact_message'] ?? ''));

// I check all required fields are filled in
if (empty($name) || empty($email) || empty($message)) {
    header('Location: index.php?msg=error#contact');
    exit;
}

// I validate the email address format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: index.php?msg=error#contact');
    exit;
}

// I build the email
$to      = 'contact@isaacadjei.me';
$subject = 'AstonCV Enquiry from ' . $name;
$body    = "Name: $name\nEmail: $email\n\nMessage:\n$message";
$headers = "From: noreply@isaacadjei.me\r\nReply-To: $email\r\n";

// I attempt to send — only works on the live server, not localhost
mail($to, $subject, $body, $headers);

header('Location: index.php?msg=sent#contact');
exit;