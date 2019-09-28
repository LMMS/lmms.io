<?php
require_once('utils.php');
require_once('dbo.php');
require_once('xhtml.php');
require_once('smtp_handler.php');
require_once('../app.php');
global $transport, $SMTP_FROM, $LSP_URL_ROOT;
global $LSP_URL;

function generate_token(string $login, string $email) {
    global $LSP_URL;
    $hash = random_str();
    $return_val = add_email_verification($login, $email, $hash);
    if ($return_val === TRUE) {
        return $hash;
    }
    return null;
}

function generate_link(string $login)
{
    global $LSP_URL, $LSP_URL_ROOT;
    $email = get_user_email($login);
    $token = generate_token($login, $email);
    if ($token === null) {
        return null;
    }
    return "$LSP_URL_ROOT?email=verify&t=$token&u=" . urlencode($login) . "&m=" . urlencode($email);
}

function generate_email(string $login) {
    global $app;
    return $app['twig']->render('email-template.twig', [
        'name' => $login,
        'link' => generate_link($login)
    ]);
}
?>

<div class="wrapper">
    <?php
        $settings_url = $LSP_URL . "?account=settings";
        $error_log = "Unknown error";
        if (SESSION() == null) {
            display_error("Please login first.",
            array("<a href=\"#\">User Settings</a>"),
            $LSP_URL
            );
            return;
        }
        if (get_if_user_email_verified(SESSION()) == 1) {
            display_success("You have already verified your email address, no need to do anything.",
            array("<a href=\"$settings_url\">User Settings</a>"),
            $settings_url);
            return;
        }
        if (can_send_email_again(SESSION()) != 1) {
            display_warning(
                'We have sent you an email just now.<br />' .
                'Please check your email inbox including spam and junk folder. <br />' .
                'If you cannot find the email, please wait a few minutes and try again.',
            array("<a href=\"$settings_url\">User Settings</a>"),
            $settings_url);
            return;
        }
        $hash = generate_email(SESSION());
        try {
            send_message(get_user_email(SESSION()), "LMMS Sharing Platform Email Verify Message", $hash);
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
    ?>
</div>