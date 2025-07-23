<?php

namespace Gzhegow\Mailer\Core\Driver\Phone;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Config\AbstractConfig;


/**
 * @property bool   $isEnabled
 * @property bool   $isDebug
 *
 * @property string $phoneToIfDebug
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

    /**
     * @var string
     */
    protected $phoneToIfDebug;


    public function validation(array &$refContext = []) : bool
    {
        $isEnabled = (bool) $this->isEnabled;
        $isDebug = (bool) $this->isDebug;

        $this->isEnabled = $isEnabled;
        $this->isDebug = $isDebug;

        if ($isEnabled) {
            $theType = Lib::type();

            if ($isDebug) {
                $theType->phone_real($this->phoneToIfDebug, $region = '')->orThrow();
            }
        }

        return true;
    }
}
