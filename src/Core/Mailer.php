<?php

namespace Gzhegow\Mailer\Core;

use Gzhegow\Mailer\Core\Struct\GenericMessage;
use Gzhegow\Mailer\Core\Driver\DriverInterface;
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
     * @param class-string<T>|DriverInterface          $driver
     * @param GenericMessage|string|array|SymfonyEmail $message
     */
    public static function sendLaterBy($driver, $message, $to = null, $context = null) : DriverInterface
    {
        return static::$facade->sendLaterBy($driver, $message, $to, $context);
    }

    /**
     * @param class-string<T>|DriverInterface          $driver
     * @param GenericMessage|string|array|SymfonyEmail $message
     */
    public static function sendNowBy($driver, $message, $to = null, $context = null) : DriverInterface
    {
        return static::$facade->sendNowBy($driver, $message, $to, $context);
    }


    /**
     * @param GenericMessage|string|array|SymfonyEmail $message
     */
    public static function interpolateMessage($message, array $placeholders = null, $context = null) : GenericMessage
    {
        return static::$facade->interpolateMessage($message, $placeholders, $context);
    }


    public static function setFacade(?MailerInterface $facade) : ?MailerInterface
    {
        $last = static::$facade;

        static::$facade = $facade;

        return $last;
    }

    /**
     * @var MailerInterface
     */
    protected static $facade;
}
