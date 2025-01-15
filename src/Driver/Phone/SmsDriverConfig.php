<?php

namespace Gzhegow\Mailer\Driver\Phone;

use Gzhegow\Lib\Config\AbstractConfig;


/**
 * @property bool $isDebug
 */
class SmsDriverConfig extends AbstractConfig
{
    /**
     * @var bool
     */
    protected $isDebug;


    public function validate() : void
    {
        $this->isDebug = (bool) $this->isDebug;
    }
}
