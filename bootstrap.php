<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/app/helpers/ResponseHelper.php';

use Dotenv\Dotenv;
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

define('APP_URL', $_ENV['APP_URL']);
define('APP_NAME', $_ENV['APP_NAME']);
define('APP_SESSION_NAME', $_ENV['APP_SESSION_NAME']);
date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'America/Bogota');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

