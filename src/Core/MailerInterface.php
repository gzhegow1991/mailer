<?php

namespace Gzhegow\Mailer\Core;

use Gzhegow\Mailer\Core\Struct\GenericMessage;
use Gzhegow\Mailer\Core\Driver\DriverInterface;
use Symfony\Component\Mime\Email as SymfonyEmail;


/**
 * @template-covariant T of DriverInterface
 */
interface MailerInterface
{
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


    /**
     * @param GenericMessage|string|array|SymfonyEmail $message
     */
    public function interpolateMessage($message, array $placeholders = null, $context = null) : GenericMessage;
}
