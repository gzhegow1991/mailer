<?php

namespace Gzhegow\Mailer\Core\Driver\Email;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Config\AbstractConfig;
use Gzhegow\Mailer\Core\Exception\LogicException;


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


    protected function validation(array &$context = []) : bool
    {
        $theType = Lib::type();

        $this->isDebug = (bool) $this->isDebug;

        if (! $theType->string_not_empty($result, $this->symfonyMailerDsn)) {
            throw new LogicException(
                [ 'The `symfonyMailerDsn` should be non-empty string', $this ]
            );
        }

        if (null !== $this->symfonyMailerEmailFrom) {
            if (! filter_var($this->symfonyMailerEmailFrom, FILTER_VALIDATE_EMAIL)) {
                throw new LogicException(
                    [ 'The `symfonyMailerEmailFrom` should be valid email address', $this ]
                );
            }
        }

        if (null !== $this->symfonyMailerEmailToIfDebug) {
            if (! filter_var($this->symfonyMailerEmailToIfDebug, FILTER_VALIDATE_EMAIL)) {
                throw new LogicException(
                    [ 'The `symfonyMailerEmailToIfDebug` should be valid email address', $this ]
                );
            }
        }

        if (null !== $this->symfonyMailerFilesystemTransportDirectory) {
            if (! $theType->dirpath_realpath($result, $this->symfonyMailerFilesystemTransportDirectory)) {
                throw new LogicException(
                    [ 'The `symfonyFilesystemTransportDirectory` should be existing directory', $this ]
                );
            }
        }

        return true;
    }
}
