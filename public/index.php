<?php

use App\Kernel;

// Back-compat shim: legacy deploys ship DB creds as a PHP file of constants at
// /home/deploy/secrets/LSP_SECRETS. Translate to env vars so Symfony picks them
// up. DB_TYPE is ignored — DBAL is wired for pdo-mysql regardless.
(static function (): void {
    $secrets = getenv('LSP_SECRETS_FILE') ?: '/home/deploy/secrets/LSP_SECRETS';
    if (!is_file($secrets)) {
        return;
    }
    include $secrets;
    if (defined('DB_HOST') && defined('DB_USER') && defined('DB_PASS') && defined('DB_DATABASE') && !getenv('DATABASE_URL')) {
        $dsn = sprintf(
            'pdo-mysql://%s:%s@%s:3306/%s?charset=utf8mb4',
            rawurlencode(DB_USER),
            rawurlencode(DB_PASS),
            DB_HOST,
            rawurlencode(DB_DATABASE),
        );
        $_SERVER['DATABASE_URL'] = $_ENV['DATABASE_URL'] = $dsn;
    }
    if (defined('DATA_DIR') && !getenv('LSP_DATA_DIR')) {
        $_SERVER['LSP_DATA_DIR'] = $_ENV['LSP_DATA_DIR'] = rtrim(DATA_DIR, '/');
    }
})();

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

return function (array $context) {
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
