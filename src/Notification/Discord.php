<?php


namespace Hitrov\Notification;


use Hitrov\Exception\CurlException;
use Hitrov\Exception\NotificationException;
use Hitrov\Interfaces\NotifierInterface;

class Discord implements NotifierInterface
{
    /**
     * @param string $message
     * @return array
     * @throws CurlException|NotificationException
     */
    public function notify(string $message): array
    {
        $webhookUrl = getenv('DISCORD_WEBHOOK_URL');
        $mentionId = getenv('DISCORD_MENTION_ID');
        $gifUrl = getenv('DISCORD_GIF_URL');

        $payload = [
            'content' => $this->mention($mentionId) . $message,
            'allowed_mentions' => ['parse' => ['users']],
        ];

        if ($gifUrl) {
            $payload['embeds'] = [
                ['image' => ['url' => $gifUrl]],
            ];
        }

        $body = json_encode($payload);

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $webhookUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_MAXREDIRS => 1,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => $body,
        ]);

        $response = curl_exec($curl);
        $error = curl_error($curl);
        $errNo = curl_errno($curl);
        $info = curl_getinfo($curl);
        curl_close($curl);

        if ($response === false || ($error && $errNo)) {
            throw new CurlException("curl error occurred: $error, response: $response", $errNo);
        }

        if ($info['http_code'] < 200 || $info['http_code'] >= 300) {
            throw new NotificationException("Discord notification failed: $response", $info['http_code']);
        }

        return ['message' => 'sent', 'http_code' => $info['http_code']];
    }

    public function isSupported(): bool
    {
        return !empty(getenv('DISCORD_WEBHOOK_URL'));
    }

    private function mention(string $mention): string
    {
        if (!$mention) {
            return '';
        }

        return ctype_digit($mention) ? "<@$mention> " : "@$mention ";
    }
}
