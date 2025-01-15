<?php

namespace Gzhegow\Mailer\Struct;

use Gzhegow\Lib\Lib;
use Gzhegow\Mailer\Exception\LogicException;
use Symfony\Component\Mime\Email as SymfonyEmail;


class GenericMessage implements \Serializable
{
    /**
     * @var SymfonyEmail
     */
    public $symfonyEmail;


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
            ?? static::tryFromSymfonyMail($from, $context)
            ?? static::tryFromArray($from, $context)
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
    public static function tryFromSymfonyMail($from, $context = null) // : ?static
    {
        if (! is_a($from, SymfonyEmail::class)) {
            return Lib::php()->error(
                [ 'The `from` should be instance of: ' . SymfonyEmail::class, $from ]
            );
        }

        $instance = new GenericMessage();
        $instance->symfonyEmail = $from;

        return $instance;
    }

    /**
     * @return static|null
     */
    public static function tryFromArray($from, $context = null) // : ?static
    {
        if (! is_array($from)) {
            return Lib::php()->error(
                [ 'The `from` should be array', $from ]
            );
        }

        if (count($from) < 2) {
            return Lib::php()->error(
                [ 'The `from` should be array with at least 2 elements', $from ]
            );
        }

        $theParse = Lib::parse();

        $_from = array_values($from);

        [ $subject, $text, $html ] = $_from + [ 2 => '' ];

        $subject = $theParse->string_not_empty($subject);
        $text = $theParse->string_not_empty($text);
        $html = $theParse->string_not_empty($html);

        $hasText = (null !== $text);
        $hasHtml = (null !== $html);

        if (! $hasText && ! $hasHtml) {
            return Lib::php()->error(
                [ 'The `from` should contain at least one of `text` (1) or `html` (2) keys', $from ]
            );
        }

        $symfonyEmail = new SymfonyEmail();
        if (null !== $subject) $symfonyEmail->subject($subject);
        if ($hasText) $symfonyEmail->text($text);
        if ($hasHtml) $symfonyEmail->html($html);

        $instance = new GenericMessage();
        $instance->symfonyEmail = $symfonyEmail;

        return $instance;
    }

    /**
     * @return static|null
     */
    public static function tryFromString($from, $context = null) // : ?static
    {
        $theParse = Lib::parse();

        if (null === ($text = $theParse->string_not_empty($from))) {
            return Lib::php()->error(
                [ 'The `from` should be non-empty string', $from ]
            );
        }

        $symfonyEmail = new SymfonyEmail();
        $symfonyEmail->text($text);

        $instance = new GenericMessage();
        $instance->symfonyEmail = $symfonyEmail;

        return $instance;
    }


    public function __serialize() : array
    {
        $vars = get_object_vars($this);

        $vars = array_filter($vars);

        return $vars;
    }

    public function __unserialize(array $data) : void
    {
        foreach ( $data as $key => $value ) {
            $this->{$key} = $value;
        }
    }

    public function serialize()
    {
        $array = $this->__serialize();

        $data = serialize($array);

        return $data;
    }

    public function unserialize($data)
    {
        $array = unserialize($data);

        $this->__unserialize($array);
    }


    public function getSymfonyEmail() : SymfonyEmail
    {
        return $this->symfonyEmail;
    }
}
