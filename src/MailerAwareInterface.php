<?php

namespace Gzhegow\Mailer;


interface MailerAwareInterface
{
    /**
     * @param null|MailerFacadeInterface $mailer
     *
     * @return void
     */
    public function setMailer(?MailerFacadeInterface $mailer) : void;
}
