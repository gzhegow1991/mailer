<?php

namespace Gzhegow\Mailer;

use Gzhegow\Lib\Lib;
use Gzhegow\Mailer\Driver\DriverInterface;
use Symfony\Component\Mime\Email as SymfonyEmail;


/**
 * @template-covariant T of DriverInterface
 */
class MailerFacade implements MailerFacadeInterface
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


    public function getType() : MailerTypeInterface
    {
        return $this->type;
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
            ?? Lib::php()->throw([ 'Unable to create GenericDriver', $driver ]);

        $theDriver = $this->factory->newDriver($genericDriver, $this->config);

        return $theDriver;
    }


    /**
     * @param class-string<T>|T   $driver
     * @param string|SymfonyEmail $message
     * @param mixed|null          $context
     *
     * @return T
     */
    public function sendLaterBy($driver, $message, $to = null, $context = null) : DriverInterface
    {
        $genericMessage = null
            ?? $this->type->parseMessage($message, $context)
            ?? Lib::php()->throw([ 'Unable to create GenericMessage', $driver ]);

        $theDriver = $this->getDriver($driver);

        $theDriver = $theDriver->sendLater($genericMessage, $to, $context);

        return $theDriver;
    }

    /**
     * @param class-string<T>|T   $driver
     * @param string|SymfonyEmail $message
     * @param mixed|null          $context
     *
     * @return T
     */
    public function sendNowBy($driver, $message, $to = null, $context = null) : DriverInterface
    {
        $genericMessage = null
            ?? $this->type->parseMessage($message, $context)
            ?? Lib::php()->throw([ 'Unable to create GenericMessage', $driver ]);

        $theDriver = $this->getDriver($driver);

        $theDriver = $theDriver->sendNow($genericMessage, $to, $context);

        return $theDriver;
    }
}
