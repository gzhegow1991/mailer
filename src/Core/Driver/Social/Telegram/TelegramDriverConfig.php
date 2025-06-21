<?php

namespace Gzhegow\Mailer\Core\Driver\Social\Telegram;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Config\AbstractConfig;
use Gzhegow\Mailer\Exception\LogicException;


/**
 * @property bool   $isEnabled
 *
 * @property string $telegramBotToken
 * @property string $telegramBotLogin
 *
 * @property bool   $isDebug
 * @property string $telegramChatIdIfDebug
 */
class TelegramDriverConfig extends AbstractConfig
{
    /**
     * @var bool
     */
    protected $isEnabled;

    /**
     * @var string
     */
    protected $telegramBotToken;
    /**
     * @var string
     */
    protected $telegramBotLogin;

    /**
     * @var bool
     */
    protected $isDebug;
    /**
     * @var string
     */
    protected $telegramChatIdIfDebug;


    protected function validation(array &$refContext = []) : bool
    {
        $isEnabled = (bool) $this->isEnabled;

        $this->isEnabled = $isEnabled;

        if ($isEnabled) {
            $theType = Lib::type();

            if (! $theType->string_not_empty($r, $this->telegramBotToken)) {
                throw new LogicException(
                    [ 'The `telegramBotToken` should be non-empty string', $this ]
                );
            }

            if (! $theType->string_not_empty($r, $this->telegramBotLogin)) {
                throw new LogicException(
                    [ 'The `telegramBotLogin` should be non-empty string', $this ]
                );
            }

            $isDebug = (bool) $this->isDebug;

            $this->isDebug = $isDebug;

            if ($isDebug) {
                if (! $theType->string_not_empty($r, $this->telegramChatIdIfDebug)) {
                    throw new LogicException(
                        [ 'The `telegramChatIdIfDebug` should be non-empty string', $this ]
                    );
                }
            }
        }

        return true;
    }
}
