<?php
declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createUnsafeImmutable(__DIR__, '.env');
$dotenv->safeLoad();

$webhookUrl = getenv('DISCORD_WEBHOOK_URL');
if (!$webhookUrl) {
    fwrite(STDERR, "DISCORD_WEBHOOK_URL is not set in .env\n");
    exit(1);
}

echo "Type a message and press Enter to send it to Discord.\n";
echo "Press Enter on an empty line (or Ctrl+C) to quit.\n\n";

while (($line = fgets(STDIN)) !== false) {
    $message = trim($line);
    if ($message === '') {
        break;
    }

    $curl = curl_init($webhookUrl);
    curl_setopt_array($curl, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS => json_encode(['content' => $message]),
    ]);

    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    if ($httpCode >= 200 && $httpCode < 300) {
        echo "sent\n";
    } else {
        echo "failed (HTTP $httpCode): $response\n";
    }
}
