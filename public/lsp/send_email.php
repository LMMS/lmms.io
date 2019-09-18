<?php
require_once('utils.php');
require_once('dbo.php');
require_once('xhtml.php');
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
    global $LSP_URL;
    $email = get_user_email($login);
    $token = generate_token($login, $email);
    if ($token === null) {
        return null;
    }
    return "$LSP_URL?email=verify&t=$token&u=" . urlencode($login) . "&m=" . urlencode($email);
}
?>

<div class="wrapper">
    <?php
        $settings_url = $LSP_URL . "?account=settings";
        if (can_send_email_again(SESSION()) != 1) {
            display_warning(
                'We just sent you an email not long before.<br />' .
                'Please check your email inbox including spam and junk folder. <br />' .
                'If you cannot find the email, please wait a few minutes and try again.',
            array("<a href=\"$settings_url\">User Settings</a>"),
            $settings_url);
            return;
        }
        $hash = generate_link(SESSION());
        if ($hash !== null) {
            display_success("An email with activation link has been sent to your email address.",
            array("<a href=\"$settings_url\">User Settings</a>"),
            $settings_url);
            // echo "$hash";
        } else {
            display_error("Server internal error. Please contact <a href=\"mailto:webmaster@lmms.io" . 
            "?subject=LSP Email Settings&body=FYI: Email System Problem: $link\">webmaster@lmms.io</a>.",
            array("<a href=\"$settings_url\">User Settings</a>"),
            $settings_url
            );
        }
    ?>
</div>