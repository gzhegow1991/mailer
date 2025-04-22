<?php

namespace Gzhegow\Mailer\Core\Driver\Social\Telegram;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Config\AbstractConfig;
use Gzhegow\Mailer\Core\Exception\LogicException;


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


    protected function validation(array &$context = []) : bool
    {
        $theType = Lib::type();

        $this->isDebug = (bool) $this->isDebug;

        if (! $theType->string_not_empty($result, $this->telegramBotToken)) {
            throw new LogicException(
                [ 'The `telegramBotToken` should be non-empty string', $this ]
            );
        }

        if (! $theType->string_not_empty($result, $this->telegramBotUsername)) {
            throw new LogicException(
                [ 'The `telegramBotUsername` should be non-empty string', $this ]
            );
        }

        if (! $theType->string_not_empty($result, $this->telegramChatIdIfDebug)) {
            throw new LogicException(
                [ 'The `telegramChatIdIfDebug` should be non-empty string', $this ]
            );
        }

        return true;
    }
}
