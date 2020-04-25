<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class AccessVoter extends Voter
{

    const EDIT      = 'edit';
    const DELETE    = 'delete';

    protected function supports($attribute, $subject)
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [ self::EDIT, self::DELETE ])
            && $subject instanceof \App\Entity\Advert;
    }

    protected function voteOnAttribute($attribute, $advert, TokenInterface $token)
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        if(null == $advert->getUser()){
            return false;
        }

        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case self::EDIT:
                return $advert->getUser() === $user;
                break;
            case self::DELETE:
                return $advert->getUser() === $user;
                break;
        }

        return false;
    }
}
