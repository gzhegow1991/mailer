<?php

namespace Gzhegow\Mailer\Core;

use Gzhegow\Lib\Config\AbstractConfig;
use Gzhegow\Mailer\Core\Driver\Phone\SmsDriverConfig;
use Gzhegow\Mailer\Core\Driver\Email\EmailDriverConfig;
use Gzhegow\Mailer\Core\Driver\Social\Telegram\TelegramDriverConfig;


/**
 * @property EmailDriverConfig    $emailDriver
 * @property TelegramDriverConfig $telegramDriver
 * @property SmsDriverConfig      $smsDriver
 */
class MailerConfig extends AbstractConfig
{
    /**
     * @var EmailDriverConfig
     */
    protected $emailDriver;
    /**
     * @var TelegramDriverConfig
     */
    protected $telegramDriver;
    /**
     * @var SmsDriverConfig
     */
    protected $smsDriver;


    public function __construct()
    {
        $this->emailDriver = new EmailDriverConfig();
        $this->telegramDriver = new TelegramDriverConfig();
        $this->smsDriver = new SmsDriverConfig();

        parent::__construct();
    }
}
