<?php

namespace Gzhegow\Mailer\Core\Struct;

use Gzhegow\Lib\Lib;
use Gzhegow\Mailer\Exception\LogicException;
use Symfony\Component\Mime\Email as SymfonyEmail;


class GenericMessage implements \Serializable
{
    /**
     * @var SymfonyEmail
     */
    public $symfonyEmail;

    /**
     * @var string
     */
    public $subject;

    /**
     * @var string
     */
    public $text;
    /**
     * @var string
     */
    public $textCharset;
    /**
     * @var string
     */
    public $html;
    /**
     * @var string
     */
    public $htmlCharset;

    public $attachments;

    /**
     * @var mixed
     */
    public $headers;
    /**
     * @var mixed
     */
    public $body;


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

        $instance->subject = $from->getSubject();

        $instance->text = $from->getTextBody();
        $instance->textCharset = $from->getTextCharset();
        $instance->html = $from->getHtmlBody();
        $instance->htmlCharset = $from->getHtmlCharset();

        $instance->attachments = $from->getAttachments();

        $instance->headers = $from->getHeaders();

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

        $instance = new GenericMessage();

        if (null !== $subject) $instance->subject = $subject;
        if ($hasText) $instance->text = $text;
        if ($hasHtml) $instance->html = $html;

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

        $instance = new GenericMessage();

        $instance->text = $text;

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


    public function getSubject() : string
    {
        return $this->subject;
    }


    public function getText() : ?string
    {
        return $this->text;
    }

    public function getTextCharset() : ?string
    {
        return $this->textCharset;
    }

    public function getHtml() : ?string
    {
        return $this->html;
    }

    public function getHtmlCharset() : ?string
    {
        return $this->htmlCharset;
    }


    public function getAttachments() : array
    {
        return $this->attachments;
    }
}
