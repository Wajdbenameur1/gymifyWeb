<?php
namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class EmailService
{
    private MailerInterface $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function sendEmail(string $to, string $subject, string $content): void
    {
        $email = (new Email())
            ->from('noreply@gymify.com') // Remplace par ton adresse expéditeur
            ->to($to)
            ->subject($subject)
            ->text($content);

        $this->mailer->send($email);
    }
}
