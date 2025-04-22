<?php

namespace Gzhegow\Mailer\Core;

use Gzhegow\Mailer\Core\Struct\GenericDriver;
use Gzhegow\Mailer\Core\Driver\DriverInterface;
use Gzhegow\Mailer\Core\Driver\Phone\SmsDriver;
use Gzhegow\Mailer\Exception\LogicException;
use Gzhegow\Mailer\Core\Driver\Email\EmailDriver;
use Gzhegow\Mailer\Core\Driver\Social\Telegram\TelegramDriver;


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
