<?php
declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createUnsafeImmutable(__DIR__, '.env');
$dotenv->safeLoad();

/**
 * Post a plain message to the configured Discord webhook.
 *
 * @return array{http_code:int, response:string}
 */
function discord_post(string $content): array
{
    $webhookUrl = getenv('DISCORD_WEBHOOK_URL');
    if (!$webhookUrl) {
        throw new RuntimeException('DISCORD_WEBHOOK_URL is not set in .env');
    }

    $curl = curl_init($webhookUrl);
    curl_setopt_array($curl, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS => json_encode(['content' => $content]),
    ]);

    $response = curl_exec($curl);
    $httpCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    return ['http_code' => $httpCode, 'response' => (string) $response];
}
