<?php


namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class EmailService
{
    private $mailer;
    private $fromAddress = 'no-reply@example.com';
    private $toAddress = 'your-email@example.com';
    
    /**
     * Constructor to initialize the EmailService service
     * 
     * @param MailerInterface $mailer MailerInterface service
     * 
     * @return null
     */
    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * Sends an email
     * 
     * @param string $subject Subject of the email
     * @param string $htmlText HTML content of the email
     * 
     * @return null
     */
    public function sendEmail($subject, $htmlText)
    {
        $email = (new Email())
            ->from($this->fromAddress)
            ->to('your-email@example.com')
            ->subject($subject)
            ->html($htmlText);

        $this->mailer->send($email);
    }
}
