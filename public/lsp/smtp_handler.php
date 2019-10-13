<?php
require_once('utils.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/../vendor/autoload.php');
use Zend\Mail\Transport\Smtp as SmtpTransport;
use Zend\Mail\Transport\SmtpOptions;
use Zend\Mime\Message as MimeMessage;
use Zend\Mime\Part as MimePart;
use Zend\Mail\Message;

/*
 * Default smtp server settings.  Override with LSP_SECRETS below
 */

$SMTP_NAME = 'smtp.server';
$SMTP_HOST = 'smtp.host';
$SMTP_PORT = 587;
$SMTP_SSL = 'tls';
$SMTP_USERNAME = 'user@some.host';
$SMTP_PASSWORD = 'PASSWORD';
$SMTP_FROM = 'lsp@lmms.io';

/*
 * By default, the LSP will use the default settings defined above
 * however, for production environments, the defaults must be overridden.  This
 * is done in a separate config file defined as $LSP_CONFIG which should be out
 * of the document root and inaccessible from a webpage.
 */
$LSP_SECRET = '/home/deploy/secrets/LSP_SECRETS';
if (file_exists($LSP_SECRET)) { include($LSP_SECRET); }

$SMTP_NAME = defined('SMTP_NAME') ? SMTP_NAME : $SMTP_NAME;
$SMTP_HOST = defined('SMTP_HOST') ? SMTP_HOST : $SMTP_HOST;
$SMTP_PORT = defined('SMTP_PORT') ? SMTP_PORT : $SMTP_PORT;
$SMTP_SSL = defined('SMTP_SSL') ? SMTP_SSL : $SMTP_SSL;
$SMTP_USERNAME = defined('SMTP_USERNAME') ? SMTP_USERNAME : $SMTP_USERNAME;
$SMTP_PASSWORD = defined('SMTP_PASSWORD') ? SMTP_PASSWORD : $SMTP_PASSWORD;
$SMTP_FROM = defined('SMTP_FROM') ? SMTP_FROM : $SMTP_FROM;

// Setup SMTP transport using PLAIN authentication over TLS
$transport = new SmtpTransport();
$options   = new SmtpOptions([
    'name'              => "$SMTP_NAME",
    'host'              => "$SMTP_HOST",
    'port'              => $SMTP_PORT,
    'connection_class'  => 'plain',
    'connection_time_limit' => 300, // recreate the connection 5 minutes after connect()
    'connection_config' => [
        'username' => "$SMTP_USERNAME",
        'password' => "$SMTP_PASSWORD",
        'ssl'      => "$SMTP_SSL",
    ],
]);
$transport->setOptions($options);


function send_message(string $email, string $subject, string $message, string $fallback = null)
{
    global $transport, $SMTP_FROM;
    $html = new MimePart($message);
    $html->type = "text/html";
    $body = new MimeMessage();
    $body->setParts(array($html));
    if ($fallback) {
        $txt = new MimePart($fallback);
        $txt->type = "text/plain";
        $body->setParts(array($txt));
    }
    $packet = new Message();
    $packet->addFrom($SMTP_FROM);
    $packet->addTo($email);
    $packet->setSubject($subject);
    $packet->setBody($body);
    $transport->send($packet);
}
?>
