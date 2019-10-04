<?php
require_once('utils.php');
require_once('dbo.php');
require_once('smtp_handler.php');
require_once('../app.php');
global $SMTP_FROM, $LSP_URL_ROOT, $LSP_URL;

function generate_token(string $login, string $email) {
    global $LSP_URL;
    $hash = random_str();
    $return_val = add_email_verification($login, $email, $hash);
    if ($return_val === TRUE) {
        return $hash;
    }
    return null;
}

function generate_link(string $login, string $action = "email=verify")
{
    global $LSP_URL, $LSP_URL_ROOT;
    $email = get_user_email($login);
    $token = generate_token($login, $email);
    if ($token === null) {
        return null;
    }
    return "$LSP_URL_ROOT?" . $action . "&t=$token&u=" . urlencode($login) . "&m=" . urlencode($email);
}

function generate_email(string $login, bool $register = true) {
    global $app;
    return $app['twig']->render('email-template.twig', [
        'name' => $login,
        'link' => generate_link($login, $register ? "email=verify" : "account=forget"),
        'register' => $register
    ]);
}

function generate_email_plain(string $login, bool $register = true) {
    $message = "Hi $login,\r\n\r\n";
    if ($register) {
        $message .= "Welcome to LMMS Sharing Platform!\r\n";
        $message .= "Please visit the link below to verify your email address and complete your registration.";
    } else {
        $message .= "You have requested a password reset.\r\n";
        $message .= "Please visit the link below to reset your password.";
    }
    $message .= "\r\n\r\n" . generate_link($login, $register ? "email=verify" : "account=forget");
    $message .= "\r\n\r\nThanks,\r\nLMMS Team";
    return $message;
}

function send_email(string $login) {
    $html_mail = generate_email($login);
    $plain_mail = generate_plain_email($login);
    try {
        send_message(get_user_email($login), "LMMS Sharing Platform Email Verify Message", $html_mail, $plain_mail);
        return true;
    } catch (Throwable $e) {
        $hash = null;
        $error_log = $e->getMessage();
    }
}
?>