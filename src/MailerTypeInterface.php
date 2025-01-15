<?php

namespace Gzhegow\Mailer;

use Gzhegow\Mailer\Struct\GenericDriver;
use Gzhegow\Mailer\Struct\GenericMessage;


interface MailerTypeInterface
{
    public function parseDriver($driver, $context = null) : ?GenericDriver;

    public function parseMessage($message, $context = null) : ?GenericMessage;
}
