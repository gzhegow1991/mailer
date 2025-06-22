<?php

namespace Gzhegow\Mailer\Core;

use Gzhegow\Mailer\Exception\LogicException;
use Gzhegow\Mailer\Core\Struct\GenericDriver;
use Gzhegow\Mailer\Exception\RuntimeException;
use Gzhegow\Mailer\Core\Driver\DriverInterface;
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
                    if (! $config->emailDriver->isEnabled) {
                        throw new RuntimeException(
                            [ 'The `emailDriver` is disabled in configuration', $config ]
                        );
                    }

                    $driverObject = new EmailDriver($config->emailDriver);

                    break;

                case TelegramDriver::class:
                    if (! $config->telegramDriver->isEnabled) {
                        throw new RuntimeException(
                            [ 'The `telegramDriver` is disabled in configuration', $config ]
                        );
                    }

                    $driverObject = new TelegramDriver($config->telegramDriver);

                    break;

                // // > todo
                // case SmsDriver::class:
                //     if (! $config->smsDriver->isEnabled) {
                //         throw new RuntimeException(
                //             [ 'The `smsDriver` is disabled in configuration', $config ]
                //         );
                //     }
                //
                //     $driverObject = new \Gzhegow\Mailer\Core\Driver\Phone\SmsDriver($config->smsDriver);
                //
                //     break;
            }
        }

        if (null === $driverObject) {
            throw new LogicException(
                [ 'Unable to create driver', $driver ]
            );
        }

        return $driverObject;
    }
}
