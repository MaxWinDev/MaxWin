<?php

namespace App\Service;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;

class MailerService
{
    public function __construct(
        private readonly MailerInterface $mailer
    )
    {
    }

    /**
     * @param string $to
     * @param string $subject
     * @param string $template
     * @param array $context
     * @return void
     * @throws TransportExceptionInterface
     * @description Méthode d'envoi d'email en lui passant le destinataire, le sujet, le template et le contexte
     */
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