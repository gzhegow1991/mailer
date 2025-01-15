<?php

namespace Gzhegow\Mailer\Package\Symfony\Component\Mailer\Transport;


interface FilesystemTransportInterface
{
    /**
     * @return static
     */
    public function setDirectory(string $directory); // : static


    /**
     * @return array<string, bool>
     */
    public function flushFilesSent() : array;
}
