<?php
declare(strict_types=1);

require __DIR__ . '/discord-post.php';

$verboseFile = __DIR__ . '/verbose.on';

echo "Type a message and press Enter to send it to Discord.\n";
echo "Commands: /verbose [on|off]  - notify on every capacity attempt\n";
echo "Press Enter on an empty line (or Ctrl+C) to quit.\n\n";

while (($line = fgets(STDIN)) !== false) {
    $message = trim($line);
    if ($message === '') {
        break;
    }

    if (preg_match('#^/verbose(\s+(on|off))?$#i', $message, $matches)) {
        $arg = strtolower($matches[2] ?? '');
        if ($arg === 'on') {
            file_put_contents($verboseFile, '1');
        } elseif ($arg === 'off') {
            @unlink($verboseFile);
        } elseif (file_exists($verboseFile)) {
            @unlink($verboseFile);
        } else {
            file_put_contents($verboseFile, '1');
        }

        echo 'verbose: ' . (file_exists($verboseFile) ? 'ON' : 'OFF') . "\n";
        continue;
    }

    $result = discord_post($message);
    if ($result['http_code'] >= 200 && $result['http_code'] < 300) {
        echo "sent\n";
    } else {
        echo "failed (HTTP {$result['http_code']}): {$result['response']}\n";
    }
}
