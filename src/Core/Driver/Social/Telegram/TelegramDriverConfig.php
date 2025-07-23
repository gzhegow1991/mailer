<?php

namespace Gzhegow\Mailer\Core\Driver\Social\Telegram;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Config\AbstractConfig;


/**
 * @property bool   $isEnabled
 *
 * @property string $telegramBotToken
 * @property string $telegramBotLogin
 *
 * @property bool   $isDebug
 * @property string $telegramChatIdToIfDebug
 */
class TelegramDriverConfig extends AbstractConfig
{
    /**
     * @var bool
     */
    protected $isEnabled;
    /**
     * @var bool
     */
    protected $isDebug;

    /**
     * @var string
     */
    protected $telegramBotToken;
    /**
     * @var string
     */
    protected $telegramBotLogin;

    /**
     * @var string
     */
    protected $telegramChatIdToIfDebug;


    protected function validation(array &$refContext = []) : bool
    {
        $isEnabled = (bool) $this->isEnabled;
        $isDebug = (bool) $this->isDebug;

        $this->isEnabled = $isEnabled;
        $this->isDebug = $isDebug;

        if ($isEnabled) {
            $theType = Lib::type();

            $theType->string_not_empty($this->telegramBotToken)->orThrow();
            $theType->string_not_empty($this->telegramBotLogin)->orThrow();

            if ($isDebug) {
                $theType->string_not_empty($this->telegramChatIdToIfDebug)->orThrow();
            }
        }

        return true;
    }
}
