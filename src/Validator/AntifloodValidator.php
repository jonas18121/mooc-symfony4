<?php

/* c'est la contrainte (ex: Antiflood) qui décide par quel validateur elle doit se faire valider
Par défault une contrainte Xxx se fait valider par le validateur XxxValidator */

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\HttpFoundation\RequestStack;
use Doctrine\ORM\EntityManagerInterface;

class AntifloodValidator extends ConstraintValidator
{

    private $requestStack;
    private $em;

    /**
     * Les arguments déclarés dans la définition du service arrivent au constructeur.
     * on les enregistrent dans l'objet pour s'enservir dans la méthode validate()  
     */
    /*public function __construct(RequestStack $requestStack, EntityManagerInterface $em)
    {
        $this->requestStack = $requestStack;
        $this->em = $em;
    }*/

    /**
     * validate doit lever une violation si la valeur est invalide
     */
    public function validate($value, Constraint $constraint)
    {
        if(strlen($value) < 3)
        {
            $this->context->buildViolation($constraint->message)
                ->atPath('author')
            //déclenche l'erreur pour le formulaire avec en argument le message de la contrainte
                ->addViolation()
            ;
        }
    }
}

 /**
     * validate doit lever une violation si la valeur est invalide
     */
    /*
    public function validate($value, Constraint $constraint)
    {
        // Pour l'instant , on considère comme flood tous message de moi de 3 caractères
        if(strlen($value) < 3)
        {
            //déclenche l'erreur pour le formulaire avec en argument le message de la contrainte
            $this->context->addViolation($constraint->message);
        }
    }


    public function validate($value, Constraint $constraint)
    {
        // getCurrentRequest() appel l'objet Request via le service request_stack
        $request = $this->requestStack->getCurrentRequest();

        // getClientIp() récupère l'IP de celui qui poste
        $ip = $request->getClientIp();

        //on vérifie si cette IP a déjà posté une candidature, il y a moins de 15 secondes
        $isFlood = $this->em->getRepository('App:Application')->isFlood($ip, 15);
        var_dump($isFlood);die;

        if($isFlood)
        {
            //déclenche l'erreur pour le formulaire avec en argument le message de la contrainte
            $this->context->addViolation($constraint->message);
        }
    }
    */