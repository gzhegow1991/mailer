<?php

namespace Gzhegow\Mailer\Core\Driver\Phone;

use Gzhegow\Mailer\Core\Struct\GenericMessage;
use Gzhegow\Mailer\Core\Driver\DriverInterface;


class SmsDriver implements DriverInterface
{
    /**
     * @var SmsDriverConfig
     */
    protected $config;


    public function __construct(SmsDriverConfig $config)
    {
        $this->config = $config;
        $this->config->validate();
    }


    public function sendLater(GenericMessage $message, $to = '', $context = null) : DriverInterface
    {
        // // > todo
        //
        // $theType = Lib::type();
        //
        // if ($theType->phone($toPhone, $to)) {
        //     if ($theType->phone_fake($toPhoneFake, $toPhone)) {
        //         return $this;
        //     }
        //
        //     if ($theType->phone_real($toPhoneFake, $toPhone)) {
        //         return $this;
        //     }
        //
        //     return $this;
        // }

        return $this;
    }

    public function sendNow(GenericMessage $message, $to = '', $context = null) : DriverInterface
    {
        // // > todo
        //
        // $theType = Lib::type();
        //
        // if ($theType->phone($toPhone, $to)) {
        //     if ($theType->phone_fake($toPhoneFake, $toPhone)) {
        //         return $this;
        //     }
        //
        //     if ($theType->phone_real($toPhoneFake, $toPhone)) {
        //         return $this;
        //     }
        //
        //     return $this;
        // }

        return $this;
    }
}
