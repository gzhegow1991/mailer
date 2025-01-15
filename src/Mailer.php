<?php

namespace Gzhegow\Mailer;

use Gzhegow\Mailer\Driver\DriverInterface;
use Symfony\Component\Mime\Email as SymfonyEmail;


/**
 * @template-covariant T of DriverInterface
 */
class Mailer
{
    /**
     * @param class-string<T>|DriverInterface $driver
     */
    public static function getDriver($driver, $context = null) : DriverInterface
    {
        return static::$facade->getDriver($driver, $context);
    }


    /**
     * @param class-string<T>|DriverInterface $driver
     * @param string|SymfonyEmail             $message
     */
    public static function sendBy($driver, $message, $to = null, $context = null) : DriverInterface
    {
        return static::$facade->sendLaterBy($driver, $message, $to, $context);
    }

    /**
     * @param class-string<T>|DriverInterface $driver
     * @param string|SymfonyEmail             $message
     */
    public static function sendImmediateBy($driver, $message, $to = null, $context = null) : DriverInterface
    {
        return static::$facade->sendNowBy($driver, $message, $to, $context);
    }


    public static function setFacade(MailerFacadeInterface $facade) : ?MailerFacadeInterface
    {
        $last = static::$facade;

        static::$facade = $facade;

        return $last;
    }

    /**
     * @var MailerFacadeInterface
     */
    protected static $facade;
}
