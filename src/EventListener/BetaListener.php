<?php

/* S E R V I C E    Q U I    E C O U T E    U N    E V E N E M E N T
Service qui va dire au gestionnaire d'évènement qu'il veut écouter un évènement précis, 
afin d'éxécuter la méthode qui va bien avec.
*/

namespace App\EventListener;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use App\EventDeclenche\BetaHTMLAdder;


class BetaListener
{
    //Notre processeur
    protected $betaHTML;

    /*
    La date de fin de la version bêta
    Avant cette date , on affichera un compte a rebours (J-3 par exemple)
    Après cette date , on n'affichera plus le mot "bêta"
    */
    protected $endDate;

    public function __construct(BetaHTMLAdder $betaHTML, $endDate)
    {
        $this->betaHTML = $betaHTML;
        $this->endDate  = new \DateTime($endDate);
    }

    /*
    Si la date est antérieure à la date définie dans le constructeur , 
    on exécute BetaHTMLAdder, sinon, on ne fait rien

    l'argument de la methode est un ResponseEvent
    */
    public function processBeta(ResponseEvent $event)
    {
        //on teste si la requête est bien une requête principale(et non une sous requête)
        if(!$event->isMasterRequest()){
            return;
        }

        $remainingDays = $this->endDate->diff(new \DateTime())->days;

        if($remainingDays <= 0)
        {
            //Si la date est dépassé, on ne fait rien
            return;
        }

        //Récupère la réponse que le gestionnaire a insérée dans l'évènement
        $response = $this->betaHTML->addBeta($event->getResponse(),$remainingDays);

        //on insère la réponse modifié dans l'évènement
        //on met a jour la reponse avec la nouvelle valeur
        $event->setResponse($response);
    }

}