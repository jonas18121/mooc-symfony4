<?php

/* évènement lors un ajout de message */

namespace App\Event;

use App\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;
//use Symfony\Component\Security\Core\User\UserInterface;

class MessagePostEvent extends Event
{
    /* MessagePostEvent ce déclenchera lui même grâce a la constante POST_MESSAGE  */
    public const POST_MESSAGE = 'OC_patform.post_message';

    protected $message;
    protected $user;

    public function __construct($message, User $user = null)
    {
        $this->message  = $message;
        $this->user     = $user;
    }

    // le listener doit avoir accès au message
    public function getMessage()
    {
        return $this->message;
    } 

    // le listener doit pouvoir modifier le message
    public function setMessage($message)
    {
        return $this->message = $message;
    }

    //le listener doit avoir accès à l'utilisateur
    public function getUser()
    {
        return $this->user;
    }
}