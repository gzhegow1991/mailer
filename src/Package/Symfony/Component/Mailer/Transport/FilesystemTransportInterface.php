<?php

namespace Gzhegow\Mailer\Package\Symfony\Component\Mailer\Transport;


interface FilesystemTransportInterface
{
    /**
     * @return array<string, bool>
     */
    public function flushFilesSent() : array;
}
