<?php

namespace Gzhegow\Mailer\Core\Driver\Phone;

use Gzhegow\Lib\Config\AbstractConfig;


/**
 * @property bool $isEnabled
 *
 * @property bool $isDebug
 */
class SmsDriverConfig extends AbstractConfig
{
    /**
     * @var bool
     */
    protected $isEnabled;

    /**
     * @var bool
     */
    protected $isDebug;


    public function validation(array &$refContext = []) : bool
    {
        $isEnabled = (bool) $this->isEnabled;

        $this->isEnabled = $isEnabled;

        if ($isEnabled) {
            $isDebug = (bool) $this->isDebug;

            $this->isDebug = $isDebug;
        }

        return true;
    }
}
