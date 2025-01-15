<?php

namespace Gzhegow\Mailer;

use Gzhegow\Lib\Config\AbstractConfig;
use Gzhegow\Mailer\Driver\Phone\SmsDriverConfig;
use Gzhegow\Mailer\Driver\Email\EmailDriverConfig;
use Gzhegow\Mailer\Driver\Social\Telegram\TelegramDriverConfig;


/**
 * @property EmailDriverConfig    $emailDriver
 * @property SmsDriverConfig      $smsDriver
 * @property TelegramDriverConfig $telegramDriver
 */
class MailerConfig extends AbstractConfig
{
    /**
     * @var EmailDriverConfig
     */
    protected $emailDriver;
    /**
     * @var SmsDriverConfig
     */
    protected $smsDriver;
    /**
     * @var TelegramDriverConfig
     */
    protected $telegramDriver;


    public function __construct()
    {
        $this->__sections[ 'emailDriver' ] = $this->emailDriver = new EmailDriverConfig();
        $this->__sections[ 'smsDriver' ] = $this->smsDriver = new SmsDriverConfig();
        $this->__sections[ 'telegramDriver' ] = $this->telegramDriver = new TelegramDriverConfig();
    }


    public function validate() : void
    {
        $this->emailDriver->validate();
        $this->smsDriver->validate();
        $this->telegramDriver->validate();
    }
}
