<?php


namespace Hitrov\Notification;


use Hitrov\Exception\CurlException;
use Hitrov\Exception\NotificationException;
use Hitrov\Interfaces\NotifierInterface;

class WhatsApp implements NotifierInterface
{
    /**
     * @param string $message
     * @return array
     * @throws CurlException|NotificationException
     */
    public function notify(string $message): array
    {
        $phone = getenv('WHATSAPP_PHONE');
        $apiKey = getenv('WHATSAPP_API_KEY');

        $url = 'https://api.callmebot.com/whatsapp.php?' . http_build_query([
            'phone' => $phone,
            'text' => $message,
            'apikey' => $apiKey,
        ]);

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_MAXREDIRS => 1,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
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
            throw new NotificationException("WhatsApp notification failed: $response", $info['http_code']);
        }

        return ['message' => 'sent', 'response' => $response];
    }

    public function isSupported(): bool
    {
        return !empty(getenv('WHATSAPP_PHONE')) && !empty(getenv('WHATSAPP_API_KEY'));
    }
}
