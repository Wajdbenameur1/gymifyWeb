<?php

namespace App\Service;

use App\Entity\User;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class EmailService
{
    private $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function sendRegistrationEmail(User $user): void
    {
        $email = (new Email())
            ->from('your-email@example.com') // Your "from" email address
            ->to($user->getEmail())          // Recipient's email address
            ->subject('Welcome to Gymify!')
            ->html(
                '<p>Dear ' . $user->getNom() . ',</p>'
                . '<p>Thank you for registering with Gymify! We are excited to have you on board.</p>'
                . '<p>Best regards,</p>'
                . '<p>The Gymify Team</p>'
            );

        $this->mailer->send($email);
    }
}
