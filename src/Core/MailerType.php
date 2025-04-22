<?php

namespace Gzhegow\Mailer\Core;

use Gzhegow\Mailer\Core\Struct\GenericDriver;
use Gzhegow\Mailer\Core\Struct\GenericMessage;


class MailerType implements MailerTypeInterface
{
    public function parseDriver($driver, $context = null) : ?GenericDriver
    {
        return GenericDriver::tryFrom($driver, $context);
    }

    public function parseMessage($message, $context = null) : ?GenericMessage
    {
        return GenericMessage::tryFrom($message, $context);
    }
}
