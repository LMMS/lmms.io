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

function send_email(string $login) {
    $hash = generate_email($login);
    try {
        send_message(get_user_email($login), "LMMS Sharing Platform Email Verify Message", $hash);
        return true;
    } catch (Throwable $e) {
        $hash = null;
        $error_log = $e->getMessage();
    }
    if ($hash !== null) {
        display_success("An email with activation link has been sent to your email address.",
        array("<a href=\"$settings_url\">User Settings</a>"),
        $settings_url);
    } else {
        display_error("Server internal error. Please contact <a href=\"mailto:webmaster@lmms.io" . 
        "?subject=LSP Email Service&body=FYI: Email System Problem: $error_log\">webmaster@lmms.io</a>.",
        array("<a href=\"$settings_url\">User Settings</a>"),
        $settings_url
        );
    }
}
?>