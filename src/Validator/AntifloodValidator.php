<?php

/* c'est la contrainte (ex: Antiflood) qui décide par quel validateur elle doit se faire valider
Par défault une contrainte Xxx se fait valider par le validateur XxxValidator */

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class AntifloodValidator extends ConstraintValidator
{
    /**
     * validate doit lever une violation si la valeur est invalide
     */
    public function validate($value, Constraint $constraint)
    {
        /* Pour l'instant , on considère comme flood tous message de moi de 3 caractères */
        if(strlen($value) < 3)
        {
            //déclenche l'erreur pour le formulaire avec en argument le message de la contrainte
            $this->context->addViolation($constraint->message);
        }
    }
}