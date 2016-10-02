<?php
/*
 * By default, the LSP will use the default database values defined in dbo.php.
 * however, for production environments, the defaults must be overridden.  This
 * is done in a separate config file which should be out of the document root
 * and inaccessible from a webpage.
 */
$secretsFile = getenv('LSP_SECRETS') ?: '/home/deploy/secrets/LSP_SECRETS';

if (!file_exists($secretsFile)) {
	error_log("Secrets file does not exist: {$secretsFile}");
}
else if (!is_readable($secretsFile)) {
	error_log('Secrets file is not readable: {$secretsFile}');
}
else {
	include($secretsFile);
}
