<?php

namespace Gzhegow\Mailer\Driver;

use Gzhegow\Mailer\Struct\GenericMessage;


interface DriverInterface
{
    public function sendLater(GenericMessage $message, $to = '', $context = null) : DriverInterface;

    public function sendNow(GenericMessage $message, $to = '', $context = null) : DriverInterface;
}
