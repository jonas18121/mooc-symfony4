<?php

/* S E R V I C E    Q U I    E C O U T E    U N    E V E N E M E N T
Service qui va dire au gestionnaire d'évènement qu'il veut écouter un évènement précis, afin d'éxécuter la méthode qui va bien avec.
*/

namespace App\EventListener;

use Symfony\Component\HttpFoundation\Response;

class BetaListener
{
    //Notre processeur
    protected $betaHTML;

    /*
    La date de fin de la version bêta
    Avant cette date , on affichera un compte a rebours (J- 3 par exemple)
    Après cette date , on n'affichera plus le "bêta"
    */
    protected $endDate;

}