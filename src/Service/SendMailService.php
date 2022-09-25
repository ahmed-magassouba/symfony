<?php

namespace App\Service;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

class SendMailService
{

    private $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function sendMail(string $from ,string $to,string $subject,string $body, array $context): void
    {
        $email = new TemplatedEmail();
        $email->from($from)
            ->to($to)
            ->subject($subject)
            ->htmlTemplate('emails/'.$body.'.html.twig')
            ->context($context);

        $this->mailer->send($email);
    }  
}
