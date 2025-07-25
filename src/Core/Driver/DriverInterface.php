<?php

namespace Gzhegow\Mailer\Core\Driver;

use Gzhegow\Mailer\Core\Struct\GenericMessage;


interface DriverInterface
{
    public function sendLater(GenericMessage $message, $to = '', ?array $context = null) : DriverInterface;

    public function sendNow(GenericMessage $message, $to = '', ?array $context = null) : DriverInterface;
}
