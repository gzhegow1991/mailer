<?php

namespace Gzhegow\Mailer\Core\Struct;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\Php\Result\Ret;
use Gzhegow\Lib\Modules\Php\Result\Result;
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
     * @param Ret $ret
     *
     * @return static|bool|null
     */
    public static function from($from, $ret = null)
    {
        $retCur = Result::asValue();

        $instance = null
            ?? static::fromStatic($from, $retCur)
            ?? static::fromSymfonyMail($from, $retCur)
            ?? static::fromArray($from, $retCur)
            ?? static::fromString($from, $retCur);

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
    public static function fromSymfonyMail($from, $ret = null)
    {
        if (! is_a($from, SymfonyEmail::class)) {
            return Result::err(
                $ret,
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

        return Result::ok($ret, $instance);
    }

    /**
     * @param Ret $ret
     *
     * @return static|bool|null
     */
    public static function fromArray($from, $ret = null)
    {
        if (! is_array($from)) {
            return Result::err(
                $ret,
                [ 'The `from` should be array', $from ],
                [ __FILE__, __LINE__ ]
            );
        }

        if (count($from) < 2) {
            return Result::err(
                $ret,
                [ 'The `from` should be array with at least 2 elements', $from ],
                [ __FILE__, __LINE__ ]
            );
        }

        $_from = array_values($from);

        [ $subject, $text, $html ] = $_from + [ 2 => '' ];

        $theType = Lib::type();

        $hasSubject = $theType->string_not_empty($subject, $subject);
        $hasText = $theType->string_not_empty($text, $text);
        $hasHtml = $theType->string_not_empty($html, $html);

        if (! $hasText && ! $hasHtml) {
            return Result::err(
                $ret,
                [ 'The `from` should contain at least one of `text` (1) or `html` (2) keys', $from ],
                [ __FILE__, __LINE__ ]
            );
        }

        $instance = new GenericMessage();

        if ($hasSubject) $instance->subject = $subject;
        if ($hasText) $instance->text = $text;
        if ($hasHtml) $instance->html = $html;

        return Result::ok($ret, $instance);
    }

    /**
     * @param Ret $ret
     *
     * @return static|bool|null
     */
    public static function fromString($from, $ret = null)
    {
        if (! Lib::type()->string_not_empty($messageText, $from)) {
            return Result::err(
                $ret,
                [ 'The `from` should be non-empty string', $from ],
                [ __FILE__, __LINE__ ]
            );
        }

        $instance = new GenericMessage();

        $instance->text = $messageText;

        return Result::ok($ret, $instance);
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
