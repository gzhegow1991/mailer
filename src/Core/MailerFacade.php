<?php

namespace Gzhegow\Mailer\Core;

use Gzhegow\Lib\Lib;
use Gzhegow\Mailer\Core\Struct\GenericDriver;
use Gzhegow\Mailer\Core\Struct\GenericMessage;
use Gzhegow\Mailer\Core\Driver\DriverInterface;
use Symfony\Component\Mime\Email as SymfonyEmail;


/**
 * @template-covariant T of DriverInterface
 */
class MailerFacade implements MailerInterface
{
    /**
     * @var MailerFactoryInterface
     */
    protected $factory;

    /**
     * @var MailerConfig
     */
    protected $config;


    public function __construct(
        MailerFactoryInterface $factory,
        //
        MailerConfig $config
    )
    {
        $this->factory = $factory;

        $this->config = $config;
        $this->config->validate();
    }


    /**
     * @param class-string<T>|T $driver
     *
     * @return T
     */
    public function getDriver($driver, array $context = []) : DriverInterface
    {
        $e = null;

        $genericDriver = null
            ?? GenericDriver::fromInstance($driver, [ &$e ])
            ?? GenericDriver::fromDriver($driver, $context, [ &$e ])
            ?? GenericDriver::fromString($driver, $context, [ &$e ]);

        if (null === $driver) {
            throw $e;
        }

        $driverObject = $this->factory->newDriver($genericDriver, $this->config);

        return $driverObject;
    }


    /**
     * @param class-string<T>|T                        $driver
     * @param GenericMessage|SymfonyEmail|array|string $message
     *
     * @return T
     */
    public function sendLaterBy($driver, $message, $to = null, array $context = []) : DriverInterface
    {
        $driverObject = $this->getDriver($driver, $context);

        $e = null;

        $genericMessage = null
            ?? GenericMessage::fromInstance($message, [ &$e ])
            ?? GenericMessage::fromSymfonyMail($message, [ &$e ])
            ?? GenericMessage::fromArray($message, [ &$e ])
            ?? GenericMessage::fromString($message, [ &$e ]);

        if (null === $genericMessage) {
            throw $e;
        }

        $driverObject->sendLater($genericMessage, $to, $context);

        return $driverObject;
    }

    /**
     * @param class-string<T>|T                        $driver
     * @param GenericMessage|SymfonyEmail|array|string $message
     *
     * @return T
     */
    public function sendNowBy($driver, $message, $to = null, array $context = []) : DriverInterface
    {
        $driverObject = $this->getDriver($driver, $context);

        $e = null;

        $genericMessage = null
            ?? GenericMessage::fromInstance($message, [ &$e ])
            ?? GenericMessage::fromSymfonyMail($message, [ &$e ])
            ?? GenericMessage::fromArray($message, [ &$e ])
            ?? GenericMessage::fromString($message, [ &$e ]);

        if (null === $genericMessage) {
            throw $e;
        }

        $driverObject = $driverObject->sendNow($genericMessage, $to, $context);

        return $driverObject;
    }


    /**
     * @param GenericMessage|SymfonyEmail|array|string $message
     */
    public function interpolateMessage($message, array $placeholders = null, array $context = []) : GenericMessage
    {
        $placeholders = $placeholders ?? [];

        $theInterpolator = Lib::str()->interpolator();

        $e = null;

        $genericMessage = null
            ?? GenericMessage::fromInstance($message, [ &$e ])
            ?? GenericMessage::fromSymfonyMail($message, [ &$e ])
            ?? GenericMessage::fromArray($message, [ &$e ])
            ?? GenericMessage::fromString($message, [ &$e ]);

        if (null === $genericMessage) {
            throw $e;
        }

        if (null !== $genericMessage->subject) {
            $subject = $theInterpolator->interpolate($genericMessage->subject, $placeholders);

            $genericMessage->subject = $subject;
        }

        if (null !== $genericMessage->text) {
            $text = $theInterpolator->interpolate($genericMessage->text, $placeholders);

            $genericMessage->text = $text;
        }

        if (null !== $genericMessage->html) {
            $html = $theInterpolator->interpolate($genericMessage->html, $placeholders);

            $genericMessage->html = $html;
        }

        return $genericMessage;
    }
}
