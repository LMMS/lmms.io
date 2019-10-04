<?php
require_once('utils.php');
require_once('dbo.php');
require_once('xhtml.php');
require_once('email_service.php');
global $LSP_URL;

function send_reset_email(string $email, string $login) {
    global $LSP_URL;
    display_success("If the information you entered is correct, you will be receiving an email shortly.",
    array("<a href=\"#\">User Settings</a>"),
    $LSP_URL);
    if(get_user_email($login) === $email) {
        $html_mail = generate_email($login, false);
        $plain_mail = generate_email_plain($login, false);
        try {
            send_message(get_user_email($login), "LMMS Sharing Platform Password Recovery", $html_mail, $plain_mail);
            return true;
        } catch (Throwable $e) {
            $html_mail = null;
            $error_log = $e->getMessage();
        }
    } else {
        sleep(3); // prevent from side-channel attacks
    }
    return true;
}

function reset_password(string $token, string $login, string $email, $pass, $pass2) {
    global $LSP_URL;
    if ($pass != $pass2) {
        display_error('Password mismatch');
        return;
    }
    if(try_verify_email($token, $login, $email) == TRUE) {
        change_user($login, get_user_realname($login), $pass, $email);
        display_success('Password changed successfully!');
    } else {
        display_error('Invalid or expired password reset link.');
    }
}
?>

<div class="wrapper">
<?php
if ((POST("reset") == "Submit")) {
    reset_password(POST("t"), POST("u"), POST("m"), POST("password"), POST("password2"));
    return;
}
if (!GET_EMPTY('t') && !GET_EMPTY('u') && !GET_EMPTY('m')) {
?>
<div class="col-md-9">
    <?php $form = new form($LSP_URL . '?account=forget', 'Password Reset', 'fa-list-alt'); ?>
        <div class="form-group">
        <label for="password">Password</label>
        <input type="password" name="password" class="form-control" maxlength="20" placeholder="password" />
        </div><div class="form-group">
        <label for="password2">Confirm password</label>
        <input type="password" name="password2" class="form-control" maxlength="20" placeholder="confirm password" />
        <input type="hidden" name="t" value="<?php echo GET('t'); ?>" />
        <input type="hidden" name="u" value="<?php echo GET('u'); ?>" />
        <input type="hidden" name="m" value="<?php echo GET('m'); ?>" />
        </div>
        <button type="submit" class="btn btn-primary" name="reset" value="Submit"><span class="fas fa-check"></span>&nbsp;Submit</button>&nbsp;
	<a href="<?php echo $LSP_URL; ?>" class="btn btn-warning"><span class="fas fa-times"></span>&nbsp;Cancel</a>
    </div>
    <?php $form->close(); ?>
<?php
    return;
}
if (POST("forget") == "Submit") {
    send_reset_email(POST("email"), POST("login"));
    return;
}
?>
<div class="col-md-9">
<?php $form = new form($LSP_URL . '?account=forget', 'Password Reset', 'fa-list-alt'); ?>
    <div class="form-group">
    <label for="realname">Username</label>
    <input type="text" name="login" class="form-control" maxlength="16" placeholder="username" />
    </div><div class="form-group">
    <label for="realname">Email address</label>
    <input type="email" name="email" class="form-control" maxlength="64" placeholder="email address" />
    </div>
    <button type="submit" class="btn btn-primary" name="forget" value="Submit"><span class="fas fa-check"></span>&nbsp;Submit</button>&nbsp;
<a href="<?php echo $LSP_URL; ?>" class="btn btn-warning"><span class="fas fa-times"></span>&nbsp;Cancel</a>
</div>
<?php $form->close(); ?>
</div>
