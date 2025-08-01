<?php

namespace Gzhegow\Mailer\Core\Driver;

use Gzhegow\Mailer\Core\Struct\GenericMessage;


class NullDriver implements DriverInterface
{
    public function sendLater(GenericMessage $message, $to = '', ?array $context = null) : DriverInterface
    {
        return $this;
    }

    public function sendNow(GenericMessage $message, $to = '', ?array $context = null) : DriverInterface
    {
        return $this;
    }
}
