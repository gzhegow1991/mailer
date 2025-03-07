<?php

namespace Gzhegow\Mailer\Driver\Email;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Config\AbstractConfig;


/**
 * @property bool   $isDebug
 * @property string $symfonyMailerDsn
 * @property string $symfonyMailerEmailFrom
 * @property string $symfonyMailerEmailToIfDebug
 * @property string $symfonyMailerFilesystemTransportDirectory
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
    protected $symfonyMailerEmailToIfDebug;
    /**
     * @var string
     */
    protected $symfonyMailerFilesystemTransportDirectory;


    public function validate() : void
    {
        $theParse = Lib::parse();

        $this->isDebug = (bool) $this->isDebug;

        $this->symfonyMailerDsn = null
            ?? $theParse->string_not_empty($this->symfonyMailerDsn)
            ?? Lib::throw([ 'The `symfonyMailerDsn` should be non-empty string', $this ]);

        if (null !== $this->symfonyMailerEmailFrom) {
            $this->symfonyMailerEmailFrom = null
                ?? (filter_var($this->symfonyMailerEmailFrom, FILTER_VALIDATE_EMAIL) ? $this->symfonyMailerEmailFrom : null)
                ?? Lib::throw([ 'The `symfonyMailerEmailFrom` should be valid email address', $this ]);
        }

        if (null !== $this->symfonyMailerEmailToIfDebug) {
            $this->symfonyMailerEmailToIfDebug = null
                ?? (filter_var($this->symfonyMailerEmailToIfDebug, FILTER_VALIDATE_EMAIL) ? $this->symfonyMailerEmailToIfDebug : null)
                ?? Lib::throw([ 'The `symfonyMailerEmailToIfDebug` should be valid email address', $this ]);
        }

        if (null !== $this->symfonyMailerFilesystemTransportDirectory) {
            $this->symfonyMailerFilesystemTransportDirectory = null
                ?? $theParse->dirpath_realpath($this->symfonyMailerFilesystemTransportDirectory)
                ?? Lib::throw([ 'The `symfonyFilesystemTransportDirectory` should be existing directory', $this ]);
        }
    }
}
