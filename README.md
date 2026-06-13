# Mailer

Простая обертка для почтовика, позволяющего рассылать письма по электронке или в социальные сети с возможностью добавление собственных драйверов

## Установить

```
composer require gzhegow/mailer
```

## Запустить тесты

```
php test.php
```

## Примеры и тесты

```php
<?php

define('__DIR_ROOT__', __DIR__ . '/..');

\Gzhegow\Lib\Lib::entrypoint()
    ->setAllRecommended()
    //
    ->setCustomDirRoot(__DIR_ROOT__)
    //
    ->useAll()
    //
    ->lock()
;


$theDebug = \Gzhegow\Lib\Lib::debug();
$theTest = \Gzhegow\Lib\Lib::test();


// >>> ЗАПУСК

$emailDriverDir = __DIR_ROOT__ . '/var/email';

// > сначала всегда фабрика
$factory = new \Gzhegow\Mailer\Core\MailerFactory();

// > создаем конфигурацию
$config = new \Gzhegow\Mailer\Core\MailerConfig();
$config->configure(
    static function (\Gzhegow\Mailer\Core\MailerConfig $config) use ($emailDriverDir) {
        if ( file_exists($iniFile = __DIR_ROOT__ . '/secret.ini') ) {
            $ini = parse_ini_file($iniFile, true);

            // > gzhegow, 2025.01.15, это всё ещё работает, в отличие от Google, который "по соображениям безопасности" крутит как хочет
            // > 'smtps://{yourlogin}%40yandex.by:{yourpassword}@smtp.yandex.ru:465'
            // > '{yourlogin}@yandex.by'

            // > \Gzhegow\Mailer\Core\Driver\Email\EmailDriver::class
            $config->emailDriver->isEnabled = true;
            $config->emailDriver->isDebug = true;
            $config->emailDriver->symfonyMailerDsn = $ini['emailDriver']['symfonyMailerDsn'];
            $config->emailDriver->emailFrom = $ini['emailDriver']['symfonyMailerEmailFrom'];
            $config->emailDriver->emailToIfDebug = $ini['emailDriver']['symfonyMailerEmailToIfDebug'];

            // > \Gzhegow\Mailer\Core\Driver\Phone\SmsDriver::class
            $config->smsDriver->isEnabled = false;
            $config->smsDriver->isDebug = false;
            $config->smsDriver->phoneToIfDebug = $ini['smsDriver']['phoneToIfDebug'];

            // > \Gzhegow\Mailer\Core\Driver\Social\Telegram\TelegramDriver::class
            $config->telegramDriver->isEnabled = true;
            $config->telegramDriver->isDebug = true;
            $config->telegramDriver->telegramBotToken = $ini['telegramDriver']['telegramBotToken'];
            $config->telegramDriver->telegramBotLogin = $ini['telegramDriver']['telegramBotLogin'];
            $config->telegramDriver->telegramChatIdToIfDebug = $ini['telegramDriver']['telegramChatIdToIfDebug'];

        } else {
            // > \Gzhegow\Mailer\Core\Driver\Email\EmailDriver::class
            $config->emailDriver->isEnabled = true;
            $config->emailDriver->isDebug = true;
            $config->emailDriver->symfonyMailerDsn = 'filesystem://default?directory=' . $emailDriverDir;
            $config->emailDriver->emailFrom = 'email@example.com';
            $config->emailDriver->emailToIfDebug = 'email@example.com';

            // // > \Gzhegow\Mailer\Core\Driver\Phone\SmsDriver::class
            $config->smsDriver->isEnabled = false;
            $config->smsDriver->isDebug = false;
            $config->smsDriver->phoneToIfDebug = '+375990000000';

            // > \Gzhegow\Mailer\Core\Driver\Social\Telegram\TelegramDriver::class
            $config->telegramDriver->isEnabled = true;
            $config->telegramDriver->isDebug = true;
            $config->telegramDriver->telegramBotToken = '0000000000:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA';
            $config->telegramDriver->telegramBotLogin = '{yourbot}_bot';
            $config->telegramDriver->telegramChatIdToIfDebug = '0000000000';
        }
    }
);

// > создаем фасад
$mailer = new \Gzhegow\Mailer\Core\MailerFacade(
    $factory,
    //
    $config
);

// > сохраняем фасад статически (чтобы вызывать без привязки к контейнеру)
\Gzhegow\Mailer\Core\Mailer::setFacade($mailer);



// >>> ТЕСТЫ

// > TEST
// > создаем дату, временную зону и интервал
$fn = function () use (
    $mailer, $theDebug,
    //
    $emailDriverDir
) {
    $theDebug->dump_value('TEST 1');
    echo PHP_EOL;

    $placeholders = [
        'name' => 'User',
    ];

    // > отправляем сообщение по электронной почте
    $symfonyEmail = new \Symfony\Component\Mime\Email();
    $symfonyEmail->subject('Hello!');
    $symfonyEmail->text('[ EMAIL ] Hello, {{name}}!');
    $symfonyEmail->html('<b>[ EMAIL ] Hello, {{name}}!</b>');
    $message = $mailer->interpolateMessage($symfonyEmail, $placeholders);
    $emailDriver = $mailer->sendNowBy(\Gzhegow\Mailer\Core\Driver\Email\EmailDriver::class, $message, $emailTo = 'email@example.com');
    $theDebug->dump_value($emailDriver);

    // > отправляем сообщение в телеграм
    $text = '[ Telegram ] Hello, {{name}}!';
    $message = $mailer->interpolateMessage($text, $placeholders);
    $telegramDriver = $mailer->sendNowBy(\Gzhegow\Mailer\Core\Driver\Social\Telegram\TelegramDriver::class, $message, $telegramChatId = '0000000000');
    $theDebug->dump_value($telegramDriver);

    // // > отправляем сообщение по SMS (драйвер следует наследовать и реализовать с использованием собственной АТС или сервиса отсылки SMS)
    try {
        $text = '[ SMS ] Hello, {{name}}!';
        $message = $mailer->interpolateMessage($text, $placeholders);
        $smsDriver = $mailer->sendNowBy(\Gzhegow\Mailer\Core\Driver\Phone\SmsDriver::class, $message, $mobilePhoneFake = '+375990000000');
    }
    catch ( \Throwable $e ) {
        $theDebug->dump_all_value([ '[ CATCH ]', $e->getMessage() ]);
    }

    // > очищаем папку перехваченных в режиме isDebug сообщений Email
    foreach ( \Gzhegow\Lib\Lib::fs()->dir_walk_it($emailDriverDir) as $spl ) {
        if ( $spl->getFilename() === '.gitignore' ) {
            continue;
        }

        $realpath = $spl->getRealPath();

        $spl->isFile()
            ? unlink($realpath)
            : rmdir($realpath);
    }
};
$test = $theTest->newCase($fn);
$test->expectStdout('
"TEST 1"

{ object # Gzhegow\Mailer\Core\Driver\Email\EmailDriver }
{ object # Gzhegow\Mailer\Core\Driver\Social\Telegram\TelegramDriver }
"[ CATCH ]" | "The `smsDriver` is disabled in configuration"
');
$test->run();
```

