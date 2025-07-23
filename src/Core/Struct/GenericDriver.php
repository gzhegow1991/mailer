<?php

namespace Gzhegow\Mailer\Core\Struct;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\Type\Ret;
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
     * @return static|Ret<static>
     */
    public static function from($from, array $context = [], ?array $fallback = null)
    {
        $ret = Ret::new();

        $instance = null
            ?? GenericDriver::fromStatic($from)->orNull($ret)
            ?? GenericDriver::fromDriver($from, $context)->orNull($ret)
            ?? GenericDriver::fromString($from, $context)->orNull($ret);

        if ($ret->isFail()) {
            return Ret::throw($fallback, $ret);
        }

        return Ret::ok($fallback, $instance);
    }

    /**
     * @return static|Ret<static>
     */
    public static function fromStatic($from, ?array $fallback = null)
    {
        if ($from instanceof static) {
            return Ret::ok($fallback, $from);
        }

        return Ret::throw(
            $fallback,
            [ 'The `from` should be instance of: ' . static::class, $from ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return static|Ret<static>
     */
    public static function fromDriver($from, array $context = [], ?array $fallback = null)
    {
        if (! is_a($from, DriverInterface::class)) {
            return Ret::throw(
                $fallback,
                [ 'The `from` should be instance of: ' . DriverInterface::class, $from ],
                [ __FILE__, __LINE__ ]
            );
        }

        $instance = new GenericDriver();
        $instance->driver = $from;
        $instance->driverClass = get_class($from);
        $instance->context = $context;

        return Ret::ok($fallback, $instance);
    }

    /**
     * @return static|Ret<static>
     */
    public static function fromString($from, array $context = [], ?array $fallback = null)
    {
        $theType = Lib::type();

        if (! $theType->string_not_empty($from)->isOk([ &$fromStringNotEmpty, &$ret ])) {
            return Ret::throw($fallback, $ret);
        }

        $driverClass = $fromStringNotEmpty;

        if (! is_subclass_of($driverClass, DriverInterface::class)) {
            return Ret::throw(
                $fallback,
                [ 'The `from` should be subclass of: ' . DriverInterface::class, $from ],
                [ __FILE__, __LINE__ ]
            );
        }

        $instance = new GenericDriver();
        $instance->driverClass = $from;
        $instance->context = $context;

        return Ret::ok($fallback, $instance);
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
