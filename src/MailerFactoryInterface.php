<?php

namespace Gzhegow\Mailer;

use Gzhegow\Mailer\Struct\GenericDriver;
use Gzhegow\Mailer\Driver\DriverInterface;


interface MailerFactoryInterface
{
    public function newDriver(GenericDriver $driver, MailerConfig $config) : DriverInterface;
}
