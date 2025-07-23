<?php

namespace Gzhegow\Mailer\Core\Driver\Email;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Config\AbstractConfig;
use Gzhegow\Lib\Exception\LogicException;


/**
 * @property bool   $isEnabled
 * @property bool   $isDebug
 *
 * @property string $symfonyMailerDsn
 *
 * @property string $emailFrom
 * @property string $emailNameFrom
 *
 * @property string $emailToIfDebug
 */
class EmailDriverConfig extends AbstractConfig
{
    /**
     * @var bool
     */
    protected $isEnabled;
    /**
     * @var bool
     */
    protected $isDebug;

    /**
     * @var string
     */
    protected $symfonyMailerDsn;

    /**
     * @var string|null
     */
    protected $emailFrom;
    /**
     * @var string|null
     */
    protected $emailNameFrom;

    /**
     * @var string
     */
    protected $emailToIfDebug;


    protected function validation(array &$refContext = []) : bool
    {
        $isEnabled = (bool) $this->isEnabled;
        $isDebug = (bool) $this->isDebug;

        $this->isEnabled = $isEnabled;
        $this->isDebug = $isDebug;

        if ($isEnabled) {
            $theType = Lib::type();

            $theType->string_not_empty($this->symfonyMailerDsn)->orThrow();

            if (null !== $this->emailFrom) {
                $theType->email($this->emailFrom)->orThrow();
            }

            if (null !== $this->emailNameFrom) {
                $theType->string_not_empty($this->emailNameFrom)->orThrow();

                if (preg_match('[\<\>]', $this->emailNameFrom)) {
                    throw new LogicException(
                        [
                            ''
                            . 'The `emailNameFrom` should not contain symbols:'
                            . '[ ' . implode(' ][ ', [ "&lt;", "&gt;" ]) . ' ]',
                            //
                            $this,
                        ]
                    );
                }
            }

            if ($isDebug) {
                $theType->email($this->emailToIfDebug)->orThrow();
            }
        }

        return true;
    }
}
