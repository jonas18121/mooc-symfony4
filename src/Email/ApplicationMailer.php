<?php

/* c'est un service */

namespace App\Email;

use App\Entity\Application;
use App\Repository\ApplicationRepository;

class ApplicationMailer
{

    /** @var \Swift_Mailer */
    private $mailer;

    public function __construct(\Swift_Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    public function sendNewNotification(Application $application)
    {
        $message = new \Swift_Message(
            'Nouvelle candidature',
            'Vous avez reçu une nouvelle candidature.'
        );

        $message->addTo($application->getAdvert()->getAuthor())
                ->addFrom('admin@votresite.com');

        $this->mailer->send($message);
    }
}