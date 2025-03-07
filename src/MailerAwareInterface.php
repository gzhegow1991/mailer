<?php

namespace Gzhegow\Mailer;


interface MailerAwareInterface
{
    /**
     * @param null|MailerInterface $mailer
     *
     * @return void
     */
    public function setMailer(?MailerInterface $mailer) : void;
}
