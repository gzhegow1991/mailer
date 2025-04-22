<?php

namespace Gzhegow\Mailer\Core;

use Gzhegow\Lib\Config\AbstractConfig;
use Gzhegow\Mailer\Core\Driver\Phone\SmsDriverConfig;
use Gzhegow\Mailer\Core\Driver\Email\EmailDriverConfig;
use Gzhegow\Mailer\Core\Driver\Social\Telegram\TelegramDriverConfig;


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
        $this->emailDriver = new EmailDriverConfig();
        $this->smsDriver = new SmsDriverConfig();
        $this->telegramDriver = new TelegramDriverConfig();

        parent::__construct();
    }
}
