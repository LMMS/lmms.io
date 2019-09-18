<?php
require_once('utils.php');
require_once('dbo.php');
require_once('xhtml.php');
global $LSP_URL;

function verify_token(string $token, string $login, string $email)
{
    global $LSP_URL;
    if(try_verify_email($token, $login, $email) == TRUE) {
        display_success("Email verified successfully for <b>$login</b>!",
        array(), $LSP_URL);
    } else {
        display_error('Invalid or expired verification link.');
    }
}

// t = token, u = login, m = email address
if (!GET_EMPTY('t') && !GET_EMPTY('u') && !GET_EMPTY('m')) {
    verify_token(GET('t'), html_entity_decode(GET('u')), html_entity_decode(GET('m')));
    return;
} else {
    display_error('Invalid verification link.');
    return;
}
?>
