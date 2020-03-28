<?php
/*
on a créer une classe contrainte, on pourra l'appeler dans d'autre classe sous forme d'annotation.
L' annotation @Annotation est nécessaire pour que cette nouvelle contrainte soit disponible via
les annotations dans les autres classes. 
*/

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class Antiflood extends Constraint
{
    public $message = 'Vous avez déjà posté un message il y a moins de 15 secondes, 
    merci d\' attendre un peu. en faite, votre message fait moins de 3 caractères, écrivez plus ;)';

    /**
     * comme on a transformer la classe AntifloodValidator en service,
     * il faut que la classe Antiflood l'appel pour ce faire valider
     */
    public function validateBy()
    {
        //ici on fait appel a l'alias du service
        return 'OC_platform_validator_antiflood';
    }

    /*
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }*/
}