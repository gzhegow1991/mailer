<?php

namespace Gzhegow\Mailer\Core\Driver\Phone;

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


    public function validation(array &$context = []) : bool
    {
        $this->isDebug = (bool) $this->isDebug;

        return true;
    }
}
