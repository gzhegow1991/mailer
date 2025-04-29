<?php

namespace Gzhegow\Mailer\Core\Struct;

use Gzhegow\Lib\Lib;
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
     * @return static|bool|null
     */
    public static function from($from, array $context = [], $ctx = null)
    {
        Result::parse($cur);

        $instance = null
            ?? GenericDriver::fromStatic($from, $cur)
            ?? GenericDriver::fromDriver($from, $context, $cur)
            ?? GenericDriver::fromString($from, $context, $cur);

        if ($cur->isErr()) {
            return Result::err($ctx, $cur);
        }

        return Result::ok($ctx, $instance);
    }

    /**
     * @return static|bool|null
     */
    public static function fromStatic($from, $ctx = null)
    {
        if ($from instanceof static) {
            return Result::ok($ctx, $from);
        }

        return Result::err(
            $ctx,
            [ 'The `from` should be instance of: ' . static::class, $from ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return static|bool|null
     */
    public static function fromDriver($from, array $context = [], $ctx = null)
    {
        if (! is_a($from, DriverInterface::class)) {
            return Result::err(
                $ctx,
                [ 'The `from` should be instance of: ' . DriverInterface::class, $from ],
                [ __FILE__, __LINE__ ]
            );
        }

        $instance = new GenericDriver();
        $instance->driver = $from;
        $instance->driverClass = get_class($from);
        $instance->context = $context;

        return Result::ok($ctx, $instance);
    }

    /**
     * @return static|bool|null
     */
    public static function fromString($from, array $context = [], $ctx = null)
    {
        if (! Lib::type()->string_not_empty($driverClass, $from)) {
            return Result::err(
                $ctx,
                [ 'The `from` should be non-empty string', $from ],
                [ __FILE__, __LINE__ ]
            );
        }

        if (! is_subclass_of($driverClass, DriverInterface::class)) {
            return Result::err(
                $ctx,
                [ 'The `from` should be subclass of: ' . DriverInterface::class, $from ],
                [ __FILE__, __LINE__ ]
            );
        }

        $instance = new GenericDriver();
        $instance->driverClass = $from;
        $instance->context = $context;

        return Result::ok($ctx, $instance);
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
