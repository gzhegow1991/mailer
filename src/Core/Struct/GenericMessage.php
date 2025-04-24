<?php

namespace Gzhegow\Mailer\Core\Struct;

use Gzhegow\Lib\Lib;
use Gzhegow\Mailer\Exception\LogicException;
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
     * @return static|bool|null
     */
    public static function fromInstance($from, array $refs = []) // : ?static
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
    public static function fromSymfonyMail($from, array $refs = [])
    {
        if (! is_a($from, SymfonyEmail::class)) {
            return Lib::refsError(
                $refs,
                new LogicException(
                    [ 'The `from` should be instance of: ' . SymfonyEmail::class, $from ]
                )
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

        return Lib::refsResult($refs, $instance);
    }

    /**
     * @return static|bool|null
     */
    public static function fromArray($from, array $refs = [])
    {
        if (! is_array($from)) {
            return Lib::refsError(
                $refs,
                new LogicException(
                    [ 'The `from` should be array', $from ]
                )
            );
        }

        if (count($from) < 2) {
            return Lib::refsError(
                $refs,
                new LogicException(
                    [ 'The `from` should be array with at least 2 elements', $from ]
                )
            );
        }


        $_from = array_values($from);

        [ $subject, $text, $html ] = $_from + [ 2 => '' ];

        $theType = Lib::type();

        $hasSubject = $theType->string_not_empty($subject, $subject);
        $hasText = $theType->string_not_empty($text, $text);
        $hasHtml = $theType->string_not_empty($html, $html);

        if (! $hasText && ! $hasHtml) {
            return Lib::refsError(
                $refs,
                new LogicException(
                    [ 'The `from` should contain at least one of `text` (1) or `html` (2) keys', $from ]
                )
            );
        }

        $instance = new GenericMessage();

        if ($hasSubject) $instance->subject = $subject;
        if ($hasText) $instance->text = $text;
        if ($hasHtml) $instance->html = $html;

        return Lib::refsResult($refs, $instance);
    }

    /**
     * @return static|bool|null
     */
    public static function fromString($from, array $refs = [])
    {
        if (! Lib::type()->string_not_empty($messageText, $from)) {
            return Lib::refsError(
                $refs,
                new LogicException(
                    [ 'The `from` should be non-empty string', $from ]
                )
            );
        }

        $instance = new GenericMessage();

        $instance->text = $messageText;

        return Lib::refsResult($refs, $instance);
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
