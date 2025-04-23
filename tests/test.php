<?php

define('__ROOT__', __DIR__ . '/..');

require_once __ROOT__ . '/vendor/autoload.php';


// > настраиваем PHP
ini_set('memory_limit', '32M');


// > настраиваем обработку ошибок
(new \Gzhegow\Lib\Exception\ErrorHandler())
    ->useErrorReporting()
    ->useErrorHandler()
    ->useExceptionHandler()
;


// > добавляем несколько функция для тестирования
$ffn = new class {
    function values($separator = null, ...$values) : string
    {
        return \Gzhegow\Lib\Lib::debug()->values([], $separator, ...$values);
    }


    function print(...$values) : void
    {
        echo $this->values(' | ', ...$values) . PHP_EOL;
    }


    function assert_stdout(
        \Closure $fn, array $fnArgs = [],
        string $expectedStdout = null
    ) : void
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);

        \Gzhegow\Lib\Lib::test()->assertStdout(
            $trace,
            $fn, $fnArgs,
            $expectedStdout
        );
    }
};


// >>> ЗАПУСКАЕМ!

// > сначала всегда фабрика
$factory = new \Gzhegow\Mailer\Core\MailerFactory();

// > создаем конфигурацию
$config = new \Gzhegow\Mailer\Core\MailerConfig();
$config->configure(function (\Gzhegow\Mailer\Core\MailerConfig $config) {
    $emailDriverDir = __ROOT__ . '/var/email';

    // > можно указать параметры для конкретных драйверов

    // \Gzhegow\Mailer\Core\Driver\Email\EmailDriver::class
    $config->emailDriver->isDebug = true;
    $config->emailDriver->symfonyMailerFilesystemTransportDirectory = $emailDriverDir;

    // \Gzhegow\Mailer\Core\Driver\Phone\SmsDriver::class
    $config->smsDriver->isDebug = true;

    // \Gzhegow\Mailer\Core\Driver\Social\Telegram\TelegramDriver::class
    $config->telegramDriver->isDebug = true;

    if (file_exists($iniFile = __ROOT__ . '/secret.ini')) {
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

// > создаем парсер типов
$type = new \Gzhegow\Mailer\Core\MailerType();

// > создаем фасад
$mailer = new \Gzhegow\Mailer\Core\MailerFacade(
    $factory,
    $type,
    //
    $config
);

// > сохраняем фасад статически (чтобы вызывать без привязки к контейнеру)
\Gzhegow\Mailer\Core\Mailer::setFacade($mailer);


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
    foreach ( \Gzhegow\Lib\Lib::fs()->dir_walk_it(__ROOT__ . '/var/email') as $spl ) {
        if ($spl->getFilename() === '.gitignore') {
            continue;
        }

        $realpath = $spl->getRealPath();

        $spl->isFile()
            ? unlink($realpath)
            : rmdir($realpath);
    }
};
$ffn->assert_stdout($fn, [], '
"TEST 1"

{ object # Gzhegow\Mailer\Core\Driver\Email\EmailDriver }
{ object # Gzhegow\Mailer\Core\Driver\Phone\SmsDriver }
{ object # Gzhegow\Mailer\Core\Driver\Social\Telegram\TelegramDriver }
');
