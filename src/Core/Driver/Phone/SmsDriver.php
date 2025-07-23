<?php

namespace Gzhegow\Mailer\Core\Driver\Phone;

use Gzhegow\Lib\Lib;
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


    public function sendLater(GenericMessage $message, $to = '', ?array $context = null) : DriverInterface
    {
        // todo

        $this->sendNow($message, $to, $context);

        return $this;
    }

    public function sendNow(GenericMessage $message, $to = '', ?array $context = null) : DriverInterface
    {
        $theType = Lib::type();

        $isDebug = $this->config->isDebug;

        $phoneTo = null
            ?? ($isDebug ? $this->config->phoneToIfDebug : null)
            ?? ($theType->phone($to)->orThrow());

        if ($theType->phone_fake($phoneTo)->isOk()) {
            // todo

            return $this;
        }

        if ($theType->phone_real($phoneTo, $region = '')->isOk()) {
            // todo

            return $this;
        }

        return $this;
    }
}
