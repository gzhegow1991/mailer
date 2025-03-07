<?php

namespace Gzhegow\Mailer\Package\Symfony\Component\Mailer\Transport;

use Gzhegow\Lib\Lib;
use Symfony\Component\Mailer\Transport\Dsn;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mailer\Transport\TransportFactoryInterface;


class FilesystemTransportFactory implements TransportFactoryInterface
{
    /**
     * @var string
     */
    protected $directoryDefault;


    public function __construct(string $directoryDefault)
    {
        $theParse = Lib::parse();

        $_directoryDefault = null
            ?? $theParse->dirpath_realpath($directoryDefault)
            ?? Lib::throw([ 'Directory is missing: ' . $directoryDefault, $directoryDefault ]);

        $this->directoryDefault = $_directoryDefault;
    }


    public function create(Dsn $dsn) : TransportInterface
    {
        $dir = $dsn->getOption('directory', $this->directoryDefault);

        $subDir = date('ymd_H0000');
        $dir = $dir . '/' . $subDir;

        $transport = new FilesystemTransport();
        $transport->setDirectory($dir);

        return $transport;
    }

    public function supports(Dsn $dsn) : bool
    {
        if ('filesystem' !== $dsn->getScheme()) return false;
        if ('default' !== $dsn->getHost()) return false;

        return true;
    }
}
