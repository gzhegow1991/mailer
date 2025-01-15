<?php

namespace Gzhegow\Mailer\Driver\Phone;

use Gzhegow\Lib\Lib;
use Gzhegow\Mailer\Struct\GenericMessage;
use Gzhegow\Mailer\Driver\DriverInterface;
use Gzhegow\Mailer\Driver\Social\Telegram\TelegramDriver;


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

        if (false === ctype_digit($phone)) {
            return null;
        }

        if (strlen($phone) > 15) {
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
