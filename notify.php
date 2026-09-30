<?php
declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;
use Hitrov\NotifierFactory;

$envFilename = empty($argv[1]) ? '.env' : $argv[1];
$dotenv = Dotenv::createUnsafeImmutable(__DIR__, $envFilename);
$dotenv->safeLoad();

if (getenv('NOTIFY_NO_GIF')) {
    putenv('DISCORD_GIF_URL=');
}

$message = getenv('NOTIFY_MESSAGE');
if ($message === false || $message === '') {
    $message = 'OCI ARM capacity: notification';
}

NotifierFactory::create()->notify($message);
