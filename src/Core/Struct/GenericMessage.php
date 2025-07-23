<?php

namespace Gzhegow\Mailer\Core\Struct;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\Type\Ret;
use Symfony\Component\Mime\Email as SymfonyEmail;


class GenericMessage implements \Serializable
{
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


    /**
     * @return static|Ret<static>
     */
    public static function from($from, ?array $fallback = null)
    {
        $ret = Ret::new();

        $instance = null
            ?? static::fromStatic($from)->orNull($ret)
            ?? static::fromSymfonyMail($from)->orNull($ret)
            ?? static::fromArray($from)->orNull($ret)
            ?? static::fromString($from)->orNull($ret);

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
    public static function fromSymfonyMail($from, ?array $fallback = null)
    {
        if (! is_a($from, SymfonyEmail::class)) {
            return Ret::throw(
                $fallback,
                [ 'The `from` should be instance of: ' . SymfonyEmail::class, $from ],
                [ __FILE__, __LINE__ ]
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

        return Ret::ok($fallback, $instance);
    }

    /**
     * @return static|Ret<static>
     */
    public static function fromArray($from, ?array $fallback = null)
    {
        if (! is_array($from)) {
            return Ret::throw(
                $fallback,
                [ 'The `from` should be array', $from ],
                [ __FILE__, __LINE__ ]
            );
        }

        if (count($from) < 2) {
            return Ret::throw(
                $fallback,
                [ 'The `from` should be array with at least 2 elements', $from ],
                [ __FILE__, __LINE__ ]
            );
        }

        $fromArray = array_values($from);

        [ $subject, $text, $html ] = $fromArray + [ null, null, null ];

        $theType = Lib::type();

        $hasSubject = (null !== $subject);
        $hasText = (null !== $text);
        $hasHtml = (null !== $html);

        $subjectStringNotEmpty = null;
        if ($hasSubject) {
            $subjectStringNotEmpty = $theType->string_not_empty($subject)->orThrow();
        }

        if (! $hasText && ! $hasHtml) {
            return Ret::throw(
                $fallback,
                [ 'The `from` should contain at least one of `text` (1) or `html` (2) keys', $from ],
                [ __FILE__, __LINE__ ]
            );
        }

        $textStringNotEmpty = null;
        if ($hasText) {
            $textStringNotEmpty = $theType->string_not_empty($text)->orThrow();
        }

        $htmlStringNotEmpty = null;
        if ($hasHtml) {
            $htmlStringNotEmpty = $theType->string_not_empty($html)->orThrow();
        }

        $instance = new GenericMessage();

        if ($hasSubject) $instance->subject = $subjectStringNotEmpty;
        if ($hasText) $instance->text = $textStringNotEmpty;
        if ($hasHtml) $instance->html = $htmlStringNotEmpty;

        return Ret::ok($fallback, $instance);
    }

    /**
     * @return static|Ret<static>
     */
    public static function fromString($from, ?array $fallback = null)
    {
        $theType = Lib::type();

        if (! $theType->string_not_empty($from)->isOk([ &$fromStringNotEmpty, &$ret ])) {
            return Ret::throw($fallback, $ret);
        }

        $messageText = $fromStringNotEmpty;

        $instance = new GenericMessage();

        $instance->text = $messageText;

        return Ret::ok($fallback, $instance);
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
