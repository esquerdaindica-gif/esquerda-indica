<?php
declare(strict_types=1);

define('APP_ROOT', dirname(__DIR__));
define('APP_DIR', __DIR__);
define('PUBLIC_DIR', APP_ROOT . '/public');
define('UPLOAD_DIR', PUBLIC_DIR . '/uploads/fotos');

if (!is_file(APP_DIR . '/config.php')) {
    http_response_code(500);
    exit('Configuração ausente: copie app/config.example.php para app/config.php e preencha os dados.');
}
$GLOBALS['CONFIG'] = require APP_DIR . '/config.php';

function config(string $key, $default = null)
{
    $v = $GLOBALS['CONFIG'];
    foreach (explode('.', $key) as $k) {
        if (!is_array($v) || !array_key_exists($k, $v)) return $default;
        $v = $v[$k];
    }
    return $v;
}

date_default_timezone_set(config('timezone', 'America/Sao_Paulo'));
mb_internal_encoding('UTF-8');

if (config('debug')) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
}

require APP_DIR . '/lib/db.php';
require APP_DIR . '/lib/helpers.php';
require APP_DIR . '/lib/auth.php';
require APP_DIR . '/lib/perfil.php';
require APP_DIR . '/lib/foto.php';
require APP_DIR . '/lib/mail.php';
require APP_DIR . '/lib/migracoes.php';
require APP_DIR . '/lib/regras.php';

if (PHP_SAPI !== 'cli') {
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    session_name('pp_sessao');
    session_set_cookie_params([
        'lifetime' => 60 * 60 * 24 * 30,
        'path'     => (config('base_path') ?: '') . '/',
        'secure'   => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    ini_set('session.gc_maxlifetime', (string)(60 * 60 * 24 * 30));
    ini_set('session.use_strict_mode', '1');
    session_start();

    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: same-origin');
}
