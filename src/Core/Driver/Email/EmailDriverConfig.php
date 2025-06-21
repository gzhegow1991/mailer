<?php

namespace Gzhegow\Mailer\Core\Driver\Email;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Config\AbstractConfig;
use Gzhegow\Mailer\Exception\LogicException;


/**
 * @property bool   $isEnabled
 *
 * @property string $symfonyMailerDsn
 * @property string $symfonyMailerEmailFrom
 *
 * @property bool   $isDebug
 * @property string $symfonyMailerEmailToIfDebug
 */
class EmailDriverConfig extends AbstractConfig
{
    /**
     * @var bool
     */
    protected $isEnabled;

    /**
     * @var string
     */
    protected $symfonyMailerDsn;
    /**
     * @var string
     */
    protected $symfonyMailerEmailFrom;

    /**
     * @var bool
     */
    protected $isDebug;
    /**
     * @var string
     */
    protected $symfonyMailerEmailToIfDebug;


    protected function validation(array &$refContext = []) : bool
    {
        $isEnabled = (bool) $this->isEnabled;

        $this->isEnabled = $isEnabled;

        if ($isEnabled) {
            $theType = Lib::type();

            $this->isDebug = (bool) $this->isDebug;

            if (! $theType->string_not_empty($r, $this->symfonyMailerDsn)) {
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

            $isDebug = (bool) $this->isDebug;

            $this->isDebug = $isDebug;

            if ($isDebug) {
                if (! filter_var($this->symfonyMailerEmailToIfDebug, FILTER_VALIDATE_EMAIL)) {
                    throw new LogicException(
                        [ 'The `symfonyMailerEmailToIfDebug` should be valid email address', $this ]
                    );
                }
            }
        }

        return true;
    }
}
