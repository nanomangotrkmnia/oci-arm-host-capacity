<?php
declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createUnsafeImmutable(__DIR__, '.env');
$dotenv->safeLoad();

/**
 * Post a plain message to the configured Discord channel as the bot.
 *
 * @return array{http_code:int, response:string}
 */
function discord_post(string $content): array
{
    $botToken = getenv('DISCORD_BOT_TOKEN');
    $channelId = getenv('DISCORD_CHANNEL_ID');
    if (!$botToken || !$channelId) {
        throw new RuntimeException('DISCORD_BOT_TOKEN / DISCORD_CHANNEL_ID are not set in .env');
    }

    $curl = curl_init("https://discord.com/api/v10/channels/$channelId/messages");
    curl_setopt_array($curl, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            "Authorization: Bot $botToken",
        ],
        CURLOPT_POSTFIELDS => json_encode(['content' => $content]),
    ]);

    $response = curl_exec($curl);
    $httpCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    return ['http_code' => $httpCode, 'response' => (string) $response];
}
