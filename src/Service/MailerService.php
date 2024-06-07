<?php

namespace App\Service;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

class MailerService
{
    public function __construct(
        private readonly MailerInterface $mailer
    )
    {
    }

    public function sendEmail(
        string $to,
        string $subject,
        string $template,
        array  $context
    ): void
    {
        // On créé une instance de notre Email
        $email = (new TemplatedEmail())
            ->from($_ENV['MAILER_FROM'])
            ->to($to)
            ->subject($subject)
            ->htmlTemplate($template)
            ->context($context);

        // On envoie le mail
        $this->mailer->send($email);
    }
}