<?php

namespace Gzhegow\Mailer\Core;

use Gzhegow\Lib\Lib;
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
     * @var MailerTypeInterface
     */
    protected $type;

    /**
     * @var MailerConfig
     */
    protected $config;


    public function __construct(
        MailerFactoryInterface $factory,
        MailerTypeInterface $type,
        //
        MailerConfig $config
    )
    {
        $this->factory = $factory;
        $this->type = $type;

        $this->config = $config;
        $this->config->validate();
    }


    /**
     * @param class-string<T>|T $driver
     *
     * @return T
     */
    public function getDriver($driver, $context = null) : DriverInterface
    {
        $genericDriver = null
            ?? $this->type->parseDriver($driver, $context)
            ?? Lib::throw([ 'Unable to create GenericDriver', $driver ]);

        $driverObject = $this->factory->newDriver($genericDriver, $this->config);

        return $driverObject;
    }


    /**
     * @param class-string<T>|T                        $driver
     * @param GenericMessage|string|array|SymfonyEmail $message
     *
     * @return T
     */
    public function sendLaterBy($driver, $message, $to = null, $context = null) : DriverInterface
    {
        $genericMessage = null
            ?? $this->type->parseMessage($message, $context)
            ?? Lib::throw([ 'Unable to create GenericMessage', $driver ]);

        $theDriver = $this->getDriver($driver);

        $theDriver = $theDriver->sendLater($genericMessage, $to, $context);

        return $theDriver;
    }

    /**
     * @param class-string<T>|T                        $driver
     * @param GenericMessage|string|array|SymfonyEmail $message
     *
     * @return T
     */
    public function sendNowBy($driver, $message, $to = null, $context = null) : DriverInterface
    {
        $genericMessage = null
            ?? $this->type->parseMessage($message, $context)
            ?? Lib::throw([ 'Unable to create GenericMessage', $driver ]);

        $theDriver = $this->getDriver($driver);

        $theDriver = $theDriver->sendNow($genericMessage, $to, $context);

        return $theDriver;
    }


    /**
     * @param GenericMessage|string|array|SymfonyEmail $message
     */
    public function interpolateMessage($message, array $placeholders = null, $context = null) : GenericMessage
    {
        $placeholders = $placeholders ?? [];

        $theInterpolator = Lib::str()->interpolator();

        $genericMessage = null
            ?? $this->type->parseMessage($message, $context)
            ?? Lib::throw([ 'Unable to create GenericMessage', $message ]);

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
