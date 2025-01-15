<?php

namespace Gzhegow\Mailer\Driver\Email;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Config\AbstractConfig;


/**
 * @property bool   $isDebug
 * @property string $symfonyMailerDsn
 * @property string $symfonyMailerEmailFrom
 * @property string $symfonyFilesystemTransportDirectory
 */
class EmailDriverConfig extends AbstractConfig
{
    /**
     * @var bool
     */
    protected $isDebug;
    /**
     * @var string
     */
    protected $symfonyMailerDsn;
    /**
     * @var string
     */
    protected $symfonyMailerEmailFrom;
    /**
     * @var string
     */
    protected $symfonyFilesystemTransportDirectory;


    public function validate() : void
    {
        $theParse = Lib::parse();

        $this->isDebug = (bool) $this->isDebug;

        $this->symfonyMailerDsn = null
            ?? $theParse->string_not_empty($this->symfonyMailerDsn)
            ?? Lib::php()->throw([ 'The `symfonyMailerDsn` should be non-empty string', $this ]);

        $this->symfonyMailerEmailFrom = null
            ?? (filter_var($this->symfonyMailerEmailFrom, FILTER_VALIDATE_EMAIL) ? $this->symfonyMailerEmailFrom : null)
            ?? Lib::php()->throw([ 'The `symfonyMailerEmailFrom` should be valid email address', $this ]);

        $this->symfonyFilesystemTransportDirectory = null
            ?? $theParse->dirpath_realpath($this->symfonyFilesystemTransportDirectory)
            ?? Lib::php()->throw([ 'The `symfonyFilesystemTransportDirectory` should be existing directory', $this ]);
    }
}
