<?php
declare(strict_types=1);

namespace Hitrov;

use Hitrov\Interfaces\NotifierInterface;
use Hitrov\Notification\Discord;
use Hitrov\Notification\Telegram;
use Hitrov\Notification\WhatsApp;

class NotifierFactory
{
    public static function create(): NotifierInterface
    {
        foreach ([Discord::class, WhatsApp::class, Telegram::class] as $notifierClass) {
            /** @var NotifierInterface $notifier */
            $notifier = new $notifierClass();
            if ($notifier->isSupported()) {
                return $notifier;
            }
        }

        return new Telegram();
    }
}
