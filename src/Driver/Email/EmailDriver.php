<?php

namespace Gzhegow\Mailer\Driver\Email;

use Symfony\Component\Mailer\Mailer;
use Gzhegow\Mailer\Struct\GenericMessage;
use Gzhegow\Mailer\Driver\DriverInterface;
use Gzhegow\Mailer\Exception\RuntimeException;
use Symfony\Component\Mailer\Transport\Dsn as SymfonyDsn;
use Symfony\Component\Mailer\Transport\NullTransportFactory;
use Symfony\Component\Mailer\Transport\NativeTransportFactory;
use Symfony\Component\Mailer\Transport as SymfonyTransportParser;
use Symfony\Component\Mailer\Transport\SendmailTransportFactory;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransportFactory;
use Symfony\Component\Mailer\MailerInterface as SymfonyMailerInterface;
use Gzhegow\Mailer\Package\Symfony\Component\Mailer\Transport\FilesystemTransportFactory;
use Symfony\Component\Mailer\Transport\TransportInterface as SymfonyMailerTransportInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface as SymfonyTransportExceptionInterface;


class EmailDriver implements DriverInterface
{
    /**
     * @var EmailDriverConfig
     */
    protected $config;

    /**
     * @var SymfonyMailerInterface
     */
    protected $symfonyMailer;
    /**
     * @var SymfonyMailerTransportInterface
     */
    protected $symfonyMailerTransport;


    public function __construct(EmailDriverConfig $config)
    {
        $this->config = $config;
    }


    public function getMailer() : SymfonyMailerInterface
    {
        return $this->symfonyMailer;
    }

    public function getMailerTransport() : SymfonyMailerTransportInterface
    {
        return $this->symfonyMailerTransport;
    }


    public function sendLater(GenericMessage $message, $to = null, $context = null) : DriverInterface
    {
        $this->sendNow($message, $to, $context);

        return $this;
    }

    public function sendNow(GenericMessage $message, $to = null, $context = null) : DriverInterface
    {
        $mailerEmailFrom = $this->config->symfonyMailerEmailFrom;

        $symfonyEmail = $message->getSymfonyEmail();

        if (! $symfonyEmail->getFrom()) {
            if (null !== $mailerEmailFrom) {
                $symfonyEmail->from($mailerEmailFrom);
            }
        }

        if (! $symfonyEmail->getTo()) {
            $_to = (array) $to;

            $symfonyEmail->to(...$_to);
        }

        $this->symfonyMailer = $this->newSymfonyMailer();

        try {
            $this->symfonyMailer->send($symfonyEmail);
        }
        catch ( SymfonyTransportExceptionInterface $e ) {
            throw new RuntimeException($e);
        }

        return $this;
    }


    protected function newSymfonyMailer() : SymfonyMailerInterface
    {
        $filesystemTransportDirectory = $this->config->symfonyFilesystemTransportDirectory;

        $isDebug = $this->config->isDebug;

        $mailerDsn = $isDebug
            ? 'filesystem://default?directory=' . $filesystemTransportDirectory
            : $this->config->symfonyMailerDsn;

        $dsn = SymfonyDsn::fromString($mailerDsn);

        $transportParser = new SymfonyTransportParser([
            new FilesystemTransportFactory($filesystemTransportDirectory),
            //
            new NullTransportFactory(),
            new SendmailTransportFactory(),
            new EsmtpTransportFactory(),
            new NativeTransportFactory(),
        ]);

        $transport = $transportParser->fromDsnObject($dsn);

        return new Mailer($transport);
    }
}
