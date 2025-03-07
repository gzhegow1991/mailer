<?php

namespace Gzhegow\Mailer\Package\Symfony\Component\Mailer\Transport;

use Gzhegow\Lib\Lib;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mime\Email as SymfonyEmail;
use Symfony\Component\Mailer\Transport\AbstractTransport;


class FilesystemTransport extends AbstractTransport implements
    FilesystemTransportInterface
{
    const FILE_EXTENSION = 'json';


    /**
     * @var string
     */
    protected $directory;

    /**
     * @var array<string, bool>
     */
    protected $filesSent = [];


    public function __toString() : string
    {
        return 'filesystem://default?directory=' . $this->directory;
    }


    public function setDirectory(string $directory) : void
    {
        $theParse = Lib::parse();

        $dirpath = null
            ?? $theParse->dirpath($directory)
            ?? Lib::throw([ 'The `directory` should be valid directory path: ' . $directory, $directory ]);

        $this->directory = $dirpath;
    }


    /**
     * @return array<string, bool>
     */
    public function flushFilesSent() : array
    {
        $filesSent = $this->filesSent;

        $this->filesSent = [];

        return $filesSent;
    }


    protected function doSend(SentMessage $message) : void
    {
        $theJson = Lib::json();
        $theParse = Lib::parse();
        $theStr = Lib::str();

        $dirpath = $theParse->dirpath($this->directory);
        if (! file_exists($dirpath)) {
            mkdir($dirpath, 0775, true);
        }

        $email = $message->getOriginalMessage();
        if (! $email instanceof SymfonyEmail) {
            return;
        }

        $fileName = [];
        foreach ( $email->getTo() as $address ) {
            $fileName[] = $address->getAddress();
        }
        $fileName = implode('-', $fileName);
        $fileName = "{$fileName}:{$email->getSubject()}";

        $fileNamePrefix = date('ymd_His_v');
        $fileName = "{$fileNamePrefix}_{$fileName}";

        $fileName = $theStr->snake_lower($fileName);

        $fileExtension = static::FILE_EXTENSION;
        $filePath = "{$this->directory}/{$fileName}.{$fileExtension}";

        $content = [];

        /** @noinspection PhpInternalEntityUsedInspection */
        [
            $content[ 'text' ],
            $content[ 'textCharset' ],
            $content[ 'html' ],
            $content[ 'htmlCharset' ],
            $content[ 'attachments' ],
            [
                $content[ 'headers' ],
                $content[ 'body' ],
            ],
        ] = $email->__serialize();

        $content = $theJson->json_print($content);

        file_put_contents($filePath, $content);

        $this->filesSent[ $filePath ] = true;
    }
}
