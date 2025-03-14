<?php

namespace Gzhegow\Mailer\Driver\Email;

use Symfony\Component\Mailer\Mailer;
use Gzhegow\Mailer\Struct\GenericMessage;
use Symfony\Component\Mime\Part\DataPart as SymfonyDataPart;
use Gzhegow\Mailer\Driver\DriverInterface;
use Gzhegow\Mailer\Exception\RuntimeException;
use Symfony\Component\Mime\Email as SymfonyEmail;
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
        $this->config->validate();
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
        $isDebug = $this->config->isDebug;

        $symfonyEmail = new SymfonyEmail();

        if (null !== $message->headers) {
            $symfonyEmail->setHeaders($message->headers);
        }

        if (null !== $message->body) {
            $symfonyEmail->setBody($message->body);

        } else {
            if (null !== $message->subject) {
                $symfonyEmail->subject($message->subject);
            }

            if (null !== $message->text) {
                $textCharsetArgs = [];

                if (null !== $message->textCharset) {
                    $textCharsetArgs[] = $message->textCharset;
                }

                $symfonyEmail->text($message->text, ...$textCharsetArgs);
            }

            if (null !== $message->html) {
                $htmlCharsetArgs = [];

                if (null !== $message->htmlCharset) {
                    $htmlCharsetArgs[] = $message->htmlCharset;
                }

                $symfonyEmail->html($message->html, ...$htmlCharsetArgs);
            }

            if (null !== $message->attachments) {
                foreach ( $message->getAttachments() as $attachment ) {
                    if ($attachment instanceof SymfonyDataPart) {
                        $symfonyEmail->attachPart($attachment);
                    }
                }
            }
        }

        if (! $symfonyEmail->getFrom()) {
            $mailerEmailFrom = $this->config->symfonyMailerEmailFrom;

            if (null !== $mailerEmailFrom) {
                $symfonyEmail->from($mailerEmailFrom);
            }
        }

        if ($isDebug) {
            $mailerEmailTo = $this->config->symfonyMailerEmailToIfDebug;

            $symfonyEmail->to($mailerEmailTo);

        } else {
            if (! $symfonyEmail->getTo()) {
                $_to = (array) $to;

                $symfonyEmail->to(...$_to);
            }
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
        $filesystemTransportDirectory = null
            ?? $this->config->symfonyMailerFilesystemTransportDirectory
            ?? __DIR__ . '/var/email';

        $mailerDsn = $this->config->symfonyMailerDsn;

        $dsn = SymfonyDsn::fromString($mailerDsn);

        $transportParser = new SymfonyTransportParser([
            new EsmtpTransportFactory(),
            new NativeTransportFactory(),
            new SendmailTransportFactory(),
            //
            new FilesystemTransportFactory($filesystemTransportDirectory),
            new NullTransportFactory(),
        ]);

        $transport = $transportParser->fromDsnObject($dsn);

        return new Mailer($transport);
    }
}
