<?php

namespace Gzhegow\Mailer\Driver;

use Gzhegow\Mailer\Struct\GenericMessage;


class NullDriver implements DriverInterface
{
    public function sendLater(GenericMessage $message, $to = '', $context = null) : DriverInterface
    {
        return $this;
    }

    public function sendNow(GenericMessage $message, $to = '', $context = null) : DriverInterface
    {
        return $this;
    }
}
