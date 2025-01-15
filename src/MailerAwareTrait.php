<?php

namespace Gzhegow\Mailer;


trait MailerAwareTrait
{
    /**
     * @var MailerFacade
     */
    protected $mailer;


    /**
     * @param null|MailerFacadeInterface $mailer
     *
     * @return void
     */
    public function setMailer(?MailerFacadeInterface $mailer) : void
    {
        $this->mailer = $mailer;
    }
}
