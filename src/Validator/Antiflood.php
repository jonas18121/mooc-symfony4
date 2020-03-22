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
    merci d\' attendre un peu. en faite votre message fait moins de 3 caractères, écrivez plus ;)';
}