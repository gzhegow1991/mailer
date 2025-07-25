<?php

namespace Gzhegow\Mailer\Core\Driver\Social\Telegram;

use Gzhegow\Lib\Lib;
use Gzhegow\Mailer\Core\Struct\GenericMessage;
use Gzhegow\Mailer\Core\Driver\DriverInterface;
use Gzhegow\Mailer\Exception\RuntimeException;


class TelegramDriver implements DriverInterface
{
    /**
     * @var TelegramDriverConfig
     */
    protected $config;


    public function __construct(TelegramDriverConfig $config)
    {
        $this->config = $config;
        $this->config->validate();
    }


    public function sendLater(GenericMessage $message, $to = '', ?array $context = null) : DriverInterface
    {
        $this->sendNow($message, $to, $context);

        return $this;
    }

    public function sendNow(GenericMessage $message, $to = '', ?array $context = null) : DriverInterface
    {
        $theType = Lib::type();

        $isDebug = $this->config->isDebug;

        $chatId = null
            ?? ($isDebug ? $this->config->telegramChatIdToIfDebug : null)
            ?? ($theType->string_not_empty($to)->orThrow());

        $messageText = $message->getText();

        $this->apiSendMessage($chatId, $messageText, $context);

        return $this;
    }


    protected function apiSendMessage(string $chatId, string $message, ?array $context = null) : array
    {
        $theFormatJson = Lib::formatJson();

        // > @gzhegow, to get your own `chatId` - write message to your own bot, then execute
        // > https://api.telegram.org/bot<Bot_token>/getUpdates

        $botToken = $this->config->telegramBotToken;

        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL            => "https://api.telegram.org/bot{$botToken}/sendMessage",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 3,
            //
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => [
                'chat_id' => $chatId,
                'text'    => $message,
            ],
            //
            CURLOPT_HTTPHEADER     => [
                'Cache-Control: no-cache',
            ],
        ]);

        $content = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new RuntimeException(
                [ 'Curl error occured: ' . curl_error($ch) ], $ch
            );
        }

        if (200 !== ($httpCode = curl_getinfo($ch, CURLINFO_RESPONSE_CODE))) {
            throw new RuntimeException(
                [ 'Response code is not 200: ' . $httpCode, $ch ]
            );
        }

        curl_close($ch);

        $response = $theFormatJson->json_decode([], $content, true);

        return $response;
    }

    protected function apiGetUpdates($context = null) : array
    {
        $theFormatJson = Lib::formatJson();

        $botToken = $this->config->telegramBotToken;

        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL            => "https://api.telegram.org/bot{$botToken}/getUpdates",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 3,
            //
            CURLOPT_HTTPHEADER     => [
                'Cache-Control: no-cache',
            ],
        ]);

        $content = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new RuntimeException([ 'Curl error occured: ' . curl_error($ch) ], $ch);
        }

        if (200 !== ($httpCode = curl_getinfo($ch, CURLINFO_RESPONSE_CODE))) {
            throw new RuntimeException([ 'Response code is not 200: ' . $httpCode, $ch ]);
        }

        curl_close($ch);

        $response = $theFormatJson->json_decode([], $content, true);

        return $response;
    }
}
