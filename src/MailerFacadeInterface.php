<?php

namespace Gzhegow\Mailer;

use Gzhegow\Mailer\Struct\GenericMessage;
use Gzhegow\Mailer\Driver\DriverInterface;
use Symfony\Component\Mime\Email as SymfonyEmail;


/**
 * @template-covariant T of DriverInterface
 */
interface MailerFacadeInterface
{
    public function getType() : MailerTypeInterface;


    /**
     * @param class-string<T>|T $driver
     *
     * @return T
     */
    public function getDriver($driver, $context = null) : DriverInterface;


    /**
     * @param class-string<T>|T                        $driver
     * @param GenericMessage|string|array|SymfonyEmail $message
     *
     * @return T
     */
    public function sendLaterBy($driver, $message, $to = null, $context = null) : DriverInterface;

    /**
     * @param class-string<T>|T                        $driver
     * @param GenericMessage|string|array|SymfonyEmail $message
     *
     * @return T
     */
    public function sendNowBy($driver, $message, $to = null, $context = null) : DriverInterface;
}
