<?php

// > настраиваем PHP
\Gzhegow\Lib\Lib::entrypoint()
    ->setDirRoot(__DIR__ . '/..')
    ->useAllRecommended()
;



// > добавляем несколько функция для тестирования
$ffn = new class {
    function root() : string
    {
        return realpath(__DIR__ . '/..');
    }


    function values($separator = null, ...$values) : string
    {
        return \Gzhegow\Lib\Lib::debug()->dump_values([], $separator, ...$values);
    }


    function print(...$values) : void
    {
        echo $this->values(' | ', ...$values) . PHP_EOL;
    }


    function test(\Closure $fn, array $args = []) : \Gzhegow\Lib\Modules\Test\TestCase
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);

        return \Gzhegow\Lib\Lib::test()->newTestCase()
            ->fn($fn, $args)
            ->trace($trace)
        ;
    }
};



// >>> ЗАПУСКАЕМ!

$emailDriverDir = $ffn->root() . '/var/email';

// > сначала всегда фабрика
$factory = new \Gzhegow\Mailer\Core\MailerFactory();

// > создаем конфигурацию
$config = new \Gzhegow\Mailer\Core\MailerConfig();
$config->configure(
    function (\Gzhegow\Mailer\Core\MailerConfig $config) use ($ffn, $emailDriverDir) {
        if (file_exists($iniFile = $ffn->root() . '/secret.ini')) {
            $ini = parse_ini_file($iniFile, true);

            // > gzhegow, 2025.01.15, это всё ещё работает, в отличие от Google, который "по соображениям безопасности" крутит как хочет
            // > 'smtps://{yourlogin}%40yandex.by:{yourpassword}@smtp.yandex.ru:465'
            // > '{yourlogin}@yandex.by'

            // > \Gzhegow\Mailer\Core\Driver\Email\EmailDriver::class
            $config->emailDriver->isEnabled = true;
            $config->emailDriver->isDebug = true;
            $config->emailDriver->symfonyMailerDsn = $ini[ 'emailDriver' ][ 'symfonyMailerDsn' ];
            $config->emailDriver->emailFrom = $ini[ 'emailDriver' ][ 'symfonyMailerEmailFrom' ];
            $config->emailDriver->emailToIfDebug = $ini[ 'emailDriver' ][ 'symfonyMailerEmailToIfDebug' ];

            // > \Gzhegow\Mailer\Core\Driver\Phone\SmsDriver::class
            $config->smsDriver->isEnabled = false;
            $config->smsDriver->isDebug = false;
            $config->smsDriver->phoneToIfDebug = $ini[ 'smsDriver' ][ 'phoneToIfDebug' ];

            // > \Gzhegow\Mailer\Core\Driver\Social\Telegram\TelegramDriver::class
            $config->telegramDriver->isEnabled = true;
            $config->telegramDriver->isDebug = true;
            $config->telegramDriver->telegramBotToken = $ini[ 'telegramDriver' ][ 'telegramBotToken' ];
            $config->telegramDriver->telegramBotLogin = $ini[ 'telegramDriver' ][ 'telegramBotLogin' ];
            $config->telegramDriver->telegramChatIdToIfDebug = $ini[ 'telegramDriver' ][ 'telegramChatIdToIfDebug' ];

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
    $ffn,
    //
    $mailer,
    //
    $emailDriverDir
) {
    $ffn->print('TEST 1');
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
    $ffn->print($emailDriver);

    // > отправляем сообщение в телеграм
    $text = '[ Telegram ] Hello, {{name}}!';
    $message = $mailer->interpolateMessage($text, $placeholders);
    $telegramDriver = $mailer->sendNowBy(\Gzhegow\Mailer\Core\Driver\Social\Telegram\TelegramDriver::class, $message, $telegramChatId = '0000000000');
    $ffn->print($telegramDriver);

    // // > отправляем сообщение по SMS (драйвер следует наследовать и реализовать с использованием собственной АТС или сервиса отсылки SMS)
    try {
        $text = '[ SMS ] Hello, {{name}}!';
        $message = $mailer->interpolateMessage($text, $placeholders);
        $smsDriver = $mailer->sendNowBy(\Gzhegow\Mailer\Core\Driver\Phone\SmsDriver::class, $message, $mobilePhoneFake = '+375990000000');
    }
    catch ( \Throwable $e ) {
        $ffn->print('[ CATCH ]', $e->getMessage());
    }

    // > очищаем папку перехваченных в режиме isDebug сообщений Email
    foreach ( \Gzhegow\Lib\Lib::fs()->dir_walk_it($emailDriverDir) as $spl ) {
        if ($spl->getFilename() === '.gitignore') {
            continue;
        }

        $realpath = $spl->getRealPath();

        $spl->isFile()
            ? unlink($realpath)
            : rmdir($realpath);
    }
};
$test = $ffn->test($fn);
$test->expectStdout('
"TEST 1"

{ object # Gzhegow\Mailer\Core\Driver\Email\EmailDriver }
{ object # Gzhegow\Mailer\Core\Driver\Social\Telegram\TelegramDriver }
"[ CATCH ]" | "The `smsDriver` is disabled in configuration"
');
$test->run();
