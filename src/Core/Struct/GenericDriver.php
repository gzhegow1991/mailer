<?php

namespace Gzhegow\Mailer\Core\Struct;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\Php\Result\Ret;
use Gzhegow\Lib\Modules\Php\Result\Result;
use Gzhegow\Mailer\Core\Driver\DriverInterface;


/**
 * @template-covariant T of DriverInterface
 */
class GenericDriver
{
    /**
     * @var T
     */
    public $driver;
    /**
     * @var class-string<T>|T
     */
    public $driverClass;

    /**
     * @var mixed
     */
    public $context;


    private function __construct()
    {
    }


    /**
     * @param Ret $ret
     *
     * @return static|bool|null
     */
    public static function from($from, array $context = [], $ret = null)
    {
        $retCur = Result::asValue();

        $instance = null
            ?? GenericDriver::fromStatic($from, $retCur)
            ?? GenericDriver::fromDriver($from, $context, $retCur)
            ?? GenericDriver::fromString($from, $context, $retCur);

        if ($retCur->isErr()) {
            return Result::err($ret, $retCur);
        }

        return Result::ok($ret, $instance);
    }

    /**
     * @param Ret $ret
     *
     * @return static|bool|null
     */
    public static function fromStatic($from, $ret = null)
    {
        if ($from instanceof static) {
            return Result::ok($ret, $from);
        }

        return Result::err(
            $ret,
            [ 'The `from` should be instance of: ' . static::class, $from ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @param Ret $ret
     *
     * @return static|bool|null
     */
    public static function fromDriver($from, array $context = [], $ret = null)
    {
        if (! is_a($from, DriverInterface::class)) {
            return Result::err(
                $ret,
                [ 'The `from` should be instance of: ' . DriverInterface::class, $from ],
                [ __FILE__, __LINE__ ]
            );
        }

        $instance = new GenericDriver();
        $instance->driver = $from;
        $instance->driverClass = get_class($from);
        $instance->context = $context;

        return Result::ok($ret, $instance);
    }

    /**
     * @param Ret $ret
     *
     * @return static|bool|null
     */
    public static function fromString($from, array $context = [], $ret = null)
    {
        if (! Lib::type()->string_not_empty($driverClass, $from)) {
            return Result::err(
                $ret,
                [ 'The `from` should be non-empty string', $from ],
                [ __FILE__, __LINE__ ]
            );
        }

        if (! is_subclass_of($driverClass, DriverInterface::class)) {
            return Result::err(
                $ret,
                [ 'The `from` should be subclass of: ' . DriverInterface::class, $from ],
                [ __FILE__, __LINE__ ]
            );
        }

        $instance = new GenericDriver();
        $instance->driverClass = $from;
        $instance->context = $context;

        return Result::ok($ret, $instance);
    }


    /**
     * @return T
     */
    public function getDriver() : DriverInterface
    {
        return $this->driver;
    }

    /**
     * @return class-string<T>|T
     */
    public function getDriverClass() : string
    {
        return $this->driverClass;
    }


    /**
     * @return mixed
     */
    public function getContext()
    {
        return $this->context;
    }
}
