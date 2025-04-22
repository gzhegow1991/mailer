<?php

namespace Gzhegow\Mailer\Core\Driver\Phone;

use Gzhegow\Lib\Lib;
use Gzhegow\Mailer\Core\Struct\GenericMessage;
use Gzhegow\Mailer\Core\Driver\DriverInterface;
use Gzhegow\Mailer\Core\Driver\Social\Telegram\TelegramDriver;


class SmsDriver implements DriverInterface
{
    /**
     * @var TelegramDriver
     */
    protected $telegramDriver;

    /**
     * @var SmsDriverConfig
     */
    protected $config;


    public function __construct(
        TelegramDriver $telegramDriver,
        //
        SmsDriverConfig $config
    )
    {
        $this->telegramDriver = $telegramDriver;

        $this->config = $config;
        $this->config->validate();
    }


    public function sendLater(GenericMessage $message, $to = '', $context = null) : DriverInterface
    {
        if (null === $this->parseTel($to)) {
            return $this;
        }

        if (null !== $this->parseTelFake($to)) {
            return $this;
        }

        $this->telegramDriver->sendNow($message, '', $context);

        return $this;
    }

    public function sendNow(GenericMessage $message, $to = '', $context = null) : DriverInterface
    {
        if (null === $this->parseTel($to)) {
            return $this;
        }

        if (null !== $this->parseTelFake($to)) {
            return $this;
        }

        $this->telegramDriver->sendNow($message, $to, $context);

        return $this;
    }


    protected function parseTel($tel) : ?string
    {
        if (null === ($_tel = Lib::parse()->string_not_empty($tel))) {
            return null;
        }

        if ('+' !== $_tel[ 0 ]) {
            return null;
        }

        $phone = substr($_tel, 1);

        if (false === Lib::type()->ctype_digit($_phone, $phone)) {
            return null;
        }

        if (strlen($_phone) > 15) {
            return null;
        }

        return $_tel;
    }

    protected function parseTelFake($tel) : ?string
    {
        if (null === ($_tel = $this->parseTel($tel))) {
            return null;
        }

        $telFakeStartsAtList = [
            '+37599',
            '+7999',
            '+' . date('YmdHis'),
        ];

        $telFake = null;
        foreach ( $telFakeStartsAtList as $startsAt ) {
            if (0 === strpos($_tel, $startsAt)) {
                $telFake = $_tel;

                break;
            }
        }

        return $telFake;
    }
}
