<?php

namespace Gzhegow\Mailer;


trait MailerAwareTrait
{
    /**
     * @var MailerFacade
     */
    protected $mailer;


    /**
     * @param null|MailerInterface $mailer
     *
     * @return void
     */
    public function setMailer(?MailerInterface $mailer) : void
    {
        $this->mailer = $mailer;
    }
}
