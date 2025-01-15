<?php

namespace Gzhegow\Mailer\Driver\Social\Telegram;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Config\AbstractConfig;


/**
 * @property bool   $isDebug
 * @property string $telegramBotToken
 * @property string $telegramBotUsername
 * @property string $telegramChatIdIfDebug
 */
class TelegramDriverConfig extends AbstractConfig
{
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
    protected $telegramBotUsername;
    /**
     * @var string
     */
    protected $telegramChatIdIfDebug;


    public function validate() : void
    {
        $theParse = Lib::parse();

        $this->isDebug = (bool) $this->isDebug;

        $this->telegramBotToken = null
            ?? $theParse->string_not_empty($this->telegramBotToken)
            ?? Lib::php()->throw([ 'The `telegramBotToken` should be non-empty string', $this ]);

        $this->telegramBotUsername = null
            ?? $theParse->string_not_empty($this->telegramBotUsername)
            ?? Lib::php()->throw([ 'The `telegramBotUsername` should be non-empty string', $this ]);

        $this->telegramChatIdIfDebug = null
            ?? $theParse->string_not_empty($this->telegramChatIdIfDebug)
            ?? Lib::php()->throw([ 'The `telegramChatIdIfDebug` should be non-empty string', $this ]);
    }
}
