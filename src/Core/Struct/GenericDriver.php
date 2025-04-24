<?php

namespace Gzhegow\Mailer\Core\Struct;

use Gzhegow\Lib\Lib;
use Gzhegow\Mailer\Exception\LogicException;
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
     * @return static
     */
    public static function from($from, array $context = []) // : static
    {
        $instance = static::tryFrom($from, $context, $e);

        if (null === $instance) {
            throw $e;
        }

        return $instance;
    }

    /**
     * @return static|null
     */
    public static function tryFrom($from, array $context = [], \Throwable &$e = null) // : ?static
    {
        $e = null;

        $instance = null
            ?? static::fromInstance($from, $context, [ &$e ])
            ?? static::fromDriver($from, $context, [ &$e ])
            ?? static::fromString($from, $context, [ &$e ]);

        return $instance;
    }


    /**
     * @return static|bool|null
     */
    public static function fromInstance($from, array $context = [], array $refs = [])
    {
        if ($from instanceof static) {
            return Lib::refsResult($refs, $from);
        }

        return Lib::refsError(
            $refs,
            new LogicException(
                [ 'The `from` should be instance of: ' . static::class, $from ]
            )
        );
    }

    /**
     * @return static|bool|null
     */
    public static function fromDriver($from, array $context = [], array $refs = [])
    {
        if (! is_a($from, DriverInterface::class)) {
            return Lib::refsError(
                $refs,
                new LogicException(
                    [ 'The `from` should be instance of: ' . DriverInterface::class, $from ]
                )
            );
        }

        $instance = new GenericDriver();
        $instance->driver = $from;
        $instance->driverClass = get_class($from);
        $instance->context = $context;

        return Lib::refsResult($refs, $instance);
    }

    /**
     * @return static|bool|null
     */
    public static function fromString($from, array $context = [], array $refs = [])
    {
        if (! Lib::type()->string_not_empty($driverClass, $from)) {
            return Lib::refsError(
                $refs,
                new LogicException(
                    [ 'The `from` should be non-empty string', $from ]
                )
            );
        }

        if (! is_subclass_of($driverClass, DriverInterface::class)) {
            return Lib::refsError(
                $refs,
                new LogicException(
                    [ 'The `from` should be subclass of: ' . DriverInterface::class, $from ]
                )
            );
        }

        $instance = new GenericDriver();
        $instance->driverClass = $from;
        $instance->context = $context;

        return Lib::refsResult($refs, $instance);
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
