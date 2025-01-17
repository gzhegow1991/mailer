# Mailer

Простая обертка для почтовика, позволяющего рассылать письма по электронке или в социальные сети с возможностью добавление собственных драйверов

## Установка

```
composer require gzhegow/mailer;
```

## Пример

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';


// > настраиваем PHP
ini_set('memory_limit', '32M');


// > настраиваем обработку ошибок
(new \Gzhegow\Lib\Exception\ErrorHandler())
    ->useErrorReporting()
    ->useErrorHandler()
    ->useExceptionHandler()
;


// > добавляем несколько функция для тестирования
function _debug(...$values) : string
{
    $lines = [];
    foreach ( $values as $value ) {
        $lines[] = \Gzhegow\Lib\Lib::debug()->type($value);
    }

    $ret = implode(' | ', $lines) . PHP_EOL;

    echo $ret;

    return $ret;
}

function _dump(...$values) : string
{
    $lines = [];
    foreach ( $values as $value ) {
        $lines[] = \Gzhegow\Lib\Lib::debug()->value($value);
    }

    $ret = implode(' | ', $lines) . PHP_EOL;

    echo $ret;

    return $ret;
}

function _dump_array($value, int $maxLevel = null, bool $multiline = false) : string
{
    $content = $multiline
        ? \Gzhegow\Lib\Lib::debug()->array_multiline($value, $maxLevel)
        : \Gzhegow\Lib\Lib::debug()->array($value, $maxLevel);

    $ret = $content . PHP_EOL;

    echo $ret;

    return $ret;
}

function _assert_output(
    \Closure $fn, string $expect = null
) : void
{
    $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);

    \Gzhegow\Lib\Lib::assert()->output($trace, $fn, $expect);
}

function _assert_microtime(
    \Closure $fn, float $expectMax = null, float $expectMin = null
) : void
{
    $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);

    \Gzhegow\Lib\Lib::assert()->microtime($trace, $fn, $expectMax, $expectMin);
}


// >>> ЗАПУСКАЕМ!

// > сначала всегда фабрика
$factory = new \Gzhegow\Mailer\MailerFactory();

// > создаем конфигурацию
$config = new \Gzhegow\Mailer\MailerConfig();
$config->configure(function (\Gzhegow\Mailer\MailerConfig $config) {
    $emailDriverDir = __DIR__ . '/var/email';

    // > можно указать параметры для конкретных драйверов

    // \Gzhegow\Mailer\Driver\Email\EmailDriver::class
    $config->emailDriver->isDebug = true;
    $config->emailDriver->symfonyMailerFilesystemTransportDirectory = $emailDriverDir;

    // \Gzhegow\Mailer\Driver\Phone\SmsDriver::class
    $config->smsDriver->isDebug = true;

    // \Gzhegow\Mailer\Driver\Social\Telegram\TelegramDriver::class
    $config->telegramDriver->isDebug = true;
    
    if (file_exists($iniFile = __DIR__ . '/secret.ini')) {
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
$type = new \Gzhegow\Mailer\MailerType();

// > создаем фасад
$mailer = new \Gzhegow\Mailer\MailerFacade(
    $factory,
    $type,
    //
    $config
);

// > сохраняем фасад статически (чтобы вызывать без привязки к контейнеру)
\Gzhegow\Mailer\Mailer::setFacade($mailer);


// > TEST
// > создаем дату, временную зону и интервал
$fn = function () use ($mailer) {
    _dump('TEST 1');
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
    $emailDriver = $mailer->sendNowBy(\Gzhegow\Mailer\Driver\Email\EmailDriver::class, $message, $emailTo = 'email@example.com');
    _dump($emailDriver);

    // > отправляем сообщение по SMS (драйвер следует наследовать и реализовать с использованием собственной АТС или сервиса отсылки SMS)
    $text = '[ SMS ] Hello, {{name}}!';
    $message = $mailer->interpolateMessage($text, $placeholders);
    $smsDriver = $mailer->sendNowBy(\Gzhegow\Mailer\Driver\Phone\SmsDriver::class, $message, $mobilePhoneFake = '+375990000000');
    _dump($smsDriver);

    // > отправляем сообщение в телеграм
    $text = '[ Telegram ] Hello, {{name}}!';
    $message = $mailer->interpolateMessage($text, $placeholders);
    $telegramDriver = $mailer->sendNowBy(\Gzhegow\Mailer\Driver\Social\Telegram\TelegramDriver::class, $message, $telegramChatId = '0000000000');
    _dump($telegramDriver);

    // > очищаем папку перехваченных в режиме isDebug сообщений Email
    foreach ( \Gzhegow\Lib\Lib::fs()->dir_walk_it(__DIR__ . '/var/email') as $spl ) {
        if ($spl->getFilename() === '.gitignore') {
            continue;
        }

        $realpath = $spl->getRealPath();

        $spl->isFile()
            ? unlink($realpath)
            : rmdir($realpath);
    }
};
_assert_output($fn, '
"TEST 1"

{ object # Gzhegow\Mailer\Driver\Email\EmailDriver }
{ object # Gzhegow\Mailer\Driver\Phone\SmsDriver }
{ object # Gzhegow\Mailer\Driver\Social\Telegram\TelegramDriver }
');
```