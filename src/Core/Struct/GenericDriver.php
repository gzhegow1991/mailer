<?php

namespace Gzhegow\Mailer\Core\Struct;

use Gzhegow\Lib\Lib;
use Gzhegow\Mailer\Core\Driver\DriverInterface;
use Gzhegow\Mailer\Exception\LogicException;


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
    public static function from($from, $context = null) // : static
    {
        $instance = static::tryFrom($from, $context, $error);

        if (null === $instance) {
            throw $error;
        }

        return $instance;
    }

    /**
     * @return static|null
     */
    public static function tryFrom($from, $context = null, \Throwable &$last = null) // : ?static
    {
        $last = null;

        Lib::php()->errors_start($b);

        $instance = null
            ?? static::tryFromInstance($from, $context)
            ?? static::tryFromDriver($from, $context)
            ?? static::tryFromString($from, $context);

        $errors = Lib::php()->errors_end($b);

        if (null === $instance) {
            foreach ( $errors as $error ) {
                $last = new LogicException($error, $last);
            }
        }

        return $instance;
    }


    /**
     * @return static|null
     */
    public static function tryFromInstance($from, $context = null) // : ?static
    {
        if (! is_a($from, static::class)) {
            return Lib::php()->error(
                [ 'The `from` should be instance of: ' . static::class, $from ]
            );
        }

        return $from;
    }

    /**
     * @return static|null
     */
    public static function tryFromDriver($from, $context = null) // : ?static
    {
        if (! is_a($from, DriverInterface::class)) {
            return Lib::php()->error(
                [ 'The `from` should be instance of: ' . DriverInterface::class, $from ]
            );
        }

        $instance = new GenericDriver();
        $instance->driver = $from;
        $instance->driverClass = get_class($from);
        $instance->context = $context;

        return $instance;
    }

    /**
     * @return static|null
     */
    public static function tryFromString($from, $context = null) // : ?static
    {
        $theParse = Lib::parse();

        if (null === ($driverClass = $theParse->string_not_empty($from))) {
            return Lib::php()->error(
                [ 'The `from` should be class-string of: ' . DriverInterface::class, $from ]
            );
        }

        if (! is_subclass_of($driverClass, DriverInterface::class)) {
            return Lib::php()->error(
                [ 'The `from` should be class-string of: ' . DriverInterface::class, $from ]
            );
        }

        $instance = new GenericDriver();
        $instance->driverClass = $from;
        $instance->context = $context;

        return $instance;
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
