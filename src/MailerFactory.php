<?php

namespace Gzhegow\Mailer;

use Gzhegow\Mailer\Struct\GenericDriver;
use Gzhegow\Mailer\Driver\DriverInterface;
use Gzhegow\Mailer\Driver\Phone\SmsDriver;
use Gzhegow\Mailer\Exception\LogicException;
use Gzhegow\Mailer\Driver\Email\EmailDriver;
use Gzhegow\Mailer\Driver\Social\Telegram\TelegramDriver;


class MailerFactory implements MailerFactoryInterface
{
    public function newDriver(GenericDriver $driver, MailerConfig $config) : DriverInterface
    {
        $driverObject = null;

        if ($driver->driver) {
            $driverObject = $driver->driver;

        } elseif ($driver->driverClass) {
            $driverClass = $driver->driverClass;

            switch ( $driverClass ) {
                case EmailDriver::class:
                    $driverObject = new EmailDriver($config->emailDriver);

                    break;

                case SmsDriver::class:
                    $telegramDriver = new TelegramDriver($config->telegramDriver);

                    $driverObject = new SmsDriver($telegramDriver, $config->smsDriver);

                    break;

                case TelegramDriver::class:
                    $driverObject = new TelegramDriver($config->telegramDriver);

                    break;
            }
        }

        if (null === $driverObject) {
            throw new LogicException([ 'Unable to create driver', $driver ]);
        }

        return $driverObject;
    }
}
