<?php

namespace Gzhegow\Mailer\Package\Symfony\Component\Mailer\Transport;

use Symfony\Component\Mailer\Transport\Dsn;
use Gzhegow\Mailer\Exception\LogicException;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mailer\Transport\TransportFactoryInterface;


class FilesystemTransportFactory implements TransportFactoryInterface
{
    public function create(Dsn $dsn) : TransportInterface
    {
        if ($this === ($optionDirectory = $dsn->getOption('directory', $this))) {
            throw new LogicException(
                [ 'The `directory` options must be passed to DSN', $dsn ]
            );
        }

        $now = new \DateTimeImmutable();

        $subDirectory = $now->format('ymd_H0000');

        $directory = $optionDirectory . '/' . $subDirectory;

        $transport = new FilesystemTransport($directory);

        return $transport;
    }

    public function supports(Dsn $dsn) : bool
    {
        if ('filesystem' !== $dsn->getScheme()) return false;
        if ('default' !== $dsn->getHost()) return false;

        return true;
    }
}
