<?php
declare(strict_types=1);

require __DIR__ . '/discord-post.php';

$message = trim((string) stream_get_contents(STDIN));
if ($message === '') {
    exit(0);
}

discord_post($message);
