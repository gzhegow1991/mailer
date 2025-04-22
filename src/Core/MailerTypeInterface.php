<?php

namespace Gzhegow\Mailer\Core;

use Gzhegow\Mailer\Core\Struct\GenericDriver;
use Gzhegow\Mailer\Core\Struct\GenericMessage;


interface MailerTypeInterface
{
    public function parseDriver($driver, $context = null) : ?GenericDriver;

    public function parseMessage($message, $context = null) : ?GenericMessage;
}
