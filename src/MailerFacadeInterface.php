<?php

namespace Gzhegow\Mailer;

use Gzhegow\Mailer\Driver\DriverInterface;
use Symfony\Component\Mime\Email as SymfonyEmail;


/**
 * @template-covariant T of DriverInterface
 */
interface MailerFacadeInterface
{
    /**
     * @param class-string<T>|T $driver
     *
     * @return T
     */
    public function getDriver($driver, $context = null) : DriverInterface;


    /**
     * @param class-string<T>|T   $driver
     * @param string|SymfonyEmail $message
     * @param mixed|null          $context
     *
     * @return T
     */
    public function sendLaterBy($driver, $message, $to = null, $context = null) : DriverInterface;

    /**
     * @param class-string<T>|T   $driver
     * @param string|SymfonyEmail $message
     * @param mixed|null          $context
     *
     * @return T
     */
    public function sendNowBy($driver, $message, $to = null, $context = null) : DriverInterface;
}
