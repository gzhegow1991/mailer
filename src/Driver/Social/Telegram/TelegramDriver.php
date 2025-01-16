<?php

namespace Gzhegow\Mailer\Driver\Social\Telegram;

use Gzhegow\Lib\Lib;
use Gzhegow\Mailer\Struct\GenericMessage;
use Gzhegow\Mailer\Driver\DriverInterface;
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
    }


    public function sendLater(GenericMessage $message, $to = '', $context = null) : DriverInterface
    {
        $this->sendNow($message, $to, $context);

        return $this;
    }

    public function sendNow(GenericMessage $message, $to = '', $context = null) : DriverInterface
    {
        $theParse = Lib::parse();

        $contextArray = (array) $context;

        $isDebug = $contextArray[ 'isDebug' ] ?? $this->config->isDebug;
        $isDebug = (bool) $isDebug;

        $messageText = $message->getText();

        if ($isDebug) {
            $chatId = null
                ?? $theParse->string_not_empty($this->config->telegramChatIdIfDebug)
                ?? Lib::php()->throw([ 'The `config.telegram.telegramChatIdIfDebug` should be non-empty string' ]);

        } else {
            $chatId = null
                ?? $theParse->string_not_empty($to)
                ?? Lib::php()->throw([ 'The `to` should be non-empty string' ]);
        }

        $this->apiSendMessage($chatId, $messageText, $context);

        return $this;
    }


    protected function apiSendMessage(string $chatId, string $message, $context = null) : array
    {
        $theJson = Lib::json();

        // > gzhegow, to get your own `chatId` - write message to your own bot, then execute
        // > https://api.telegram.org/bot<Bot_token>/getUpdates

        $botToken = $this->config->telegramBotToken;

        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL            => "https://api.telegram.org/bot{$botToken}/sendMessage",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 3,
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

        $res = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new RuntimeException([ 'Curl error occured: ' . curl_error($ch) ], $ch);
        }

        if (200 !== ($httpCode = curl_getinfo($ch, CURLINFO_RESPONSE_CODE))) {
            throw new RuntimeException([ 'Response code is not 200: ' . $httpCode, $ch ]);
        }

        curl_close($ch);

        $response = $theJson->json_decode($res, true);

        return $response;
    }

    protected function apiGetUpdates($context = null) : array
    {
        $theJson = Lib::json();

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

        $res = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new RuntimeException([ 'Curl error occured: ' . curl_error($ch) ], $ch);
        }

        if (200 !== ($httpCode = curl_getinfo($ch, CURLINFO_RESPONSE_CODE))) {
            throw new RuntimeException([ 'Response code is not 200: ' . $httpCode, $ch ]);
        }

        curl_close($ch);

        $response = $theJson->json_decode($res, true);

        return $response;
    }
}
