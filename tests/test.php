<?php

require_once __DIR__ . '/../vendor/autoload.php';


// > настраиваем PHP
ini_set('memory_limit', '32M');


// > настраиваем обработку ошибок
\Gzhegow\Lib\Lib::entrypoint()
    ->setDirRoot(__DIR__ . '/..')
    //
    ->useErrorReporting()
    ->useMemoryLimit()
    ->useTimeLimit()
    ->useErrorHandler()
    ->useExceptionHandler()
;


// > добавляем несколько функция для тестирования
$ffn = new class {
    function root() : string
    {
        return realpath(__DIR__ . '/..');
    }


    function values($separator = null, ...$values) : string
    {
        return \Gzhegow\Lib\Lib::debug()->values([], $separator, ...$values);
    }


    function print(...$values) : void
    {
        echo $this->values(' | ', ...$values) . PHP_EOL;
    }


    function test(\Closure $fn, array $args = []) : \Gzhegow\Lib\Modules\Test\TestRunner\TestRunner
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);

        return \Gzhegow\Lib\Lib::test()->test()
            ->fn($fn, $args)
            ->trace($trace)
        ;
    }
};



// >>> ЗАПУСКАЕМ!

// > сначала всегда фабрика
$factory = new \Gzhegow\Mailer\Core\MailerFactory();

// > создаем конфигурацию
$config = new \Gzhegow\Mailer\Core\MailerConfig();
$config->configure(function (\Gzhegow\Mailer\Core\MailerConfig $config) use ($ffn) {
    $emailDriverDir = $ffn->root() . '/var/email';

    // > можно указать параметры для конкретных драйверов

    // \Gzhegow\Mailer\Core\Driver\Email\EmailDriver::class
    $config->emailDriver->isDebug = true;
    $config->emailDriver->symfonyMailerFilesystemTransportDirectory = $emailDriverDir;

    // \Gzhegow\Mailer\Core\Driver\Phone\SmsDriver::class
    $config->smsDriver->isDebug = true;

    // \Gzhegow\Mailer\Core\Driver\Social\Telegram\TelegramDriver::class
    $config->telegramDriver->isDebug = true;

    if (file_exists($iniFile = $ffn->root() . '/secret.ini')) {
        $ini = parse_ini_file($iniFile, true);

        // > gzhegow, 2025.01.15, это всё ещё работает, в отличие от Google, который "по соображениям безопасности" крутит как хочет
        // 'smtps://{yourlogin}%40yandex.by:{yourpassword}@smtp.yandex.ru:465'
        $config->emailDriver->symfonyMailerDsn = $ini[ 'emailDriver' ][ 'symfonyMailerDsn' ];
        // '{yourlogin}@yandex.by'
        $config->emailDriver->symfonyMailerEmailFrom = $ini[ 'emailDriver' ][ 'symfonyMailerEmailFrom' ];
        $config->emailDriver->symfonyMailerEmailToIfDebug = $ini[ 'emailDriver' ][ 'symfonyMailerEmailToIfDebug' ];

        $config->telegramDriver->telegramBotToken = $ini[ 'telegramDriver' ][ 'telegramBotToken' ];
        $config->telegramDriver->telegramBotUsername = $ini[ 'telegramDriver' ][ 'telegramBotUsername' ];
        $config->telegramDriver->telegramChatIdIfDebug = $ini[ 'telegramDriver' ][ 'telegramChatIdIfDebug' ];

    } else {
        $config->emailDriver->symfonyMailerDsn = 'filesystem://default?directory=' . $emailDriverDir;
        $config->emailDriver->symfonyMailerEmailFrom = 'email@example.com';
        $config->emailDriver->symfonyMailerEmailToIfDebug = 'email@example.com';

        $config->telegramDriver->telegramBotToken = '0000000000:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA';
        $config->telegramDriver->telegramBotUsername = '{yourbot}_bot';
        $config->telegramDriver->telegramChatIdIfDebug = '0000000000';
    }
});

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
$fn = function () use ($mailer, $ffn) {
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

    // > отправляем сообщение по SMS (драйвер следует наследовать и реализовать с использованием собственной АТС или сервиса отсылки SMS)
    $text = '[ SMS ] Hello, {{name}}!';
    $message = $mailer->interpolateMessage($text, $placeholders);
    $smsDriver = $mailer->sendNowBy(\Gzhegow\Mailer\Core\Driver\Phone\SmsDriver::class, $message, $mobilePhoneFake = '+375990000000');
    $ffn->print($smsDriver);

    // > отправляем сообщение в телеграм
    $text = '[ Telegram ] Hello, {{name}}!';
    $message = $mailer->interpolateMessage($text, $placeholders);
    $telegramDriver = $mailer->sendNowBy(\Gzhegow\Mailer\Core\Driver\Social\Telegram\TelegramDriver::class, $message, $telegramChatId = '0000000000');
    $ffn->print($telegramDriver);

    // > очищаем папку перехваченных в режиме isDebug сообщений Email
    foreach ( \Gzhegow\Lib\Lib::fs()->dir_walk_it($ffn->root() . '/var/email') as $spl ) {
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
{ object # Gzhegow\Mailer\Core\Driver\Phone\SmsDriver }
{ object # Gzhegow\Mailer\Core\Driver\Social\Telegram\TelegramDriver }
');
$test->run();
