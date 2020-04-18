<?php

/* S E R V I C E    Q U I    D E C L E N C H E    U N    E V E N E M E N T
Service qui déclenche un évènement et prévient le gestionnaire d'évènemént 
qu'un certain évènement vient de ce produire
*/

namespace App\EventDeclenche;

use Symfony\Component\HttpFoundation\Response;

/* on veut ajouter le mot Beta jusqu'a une date précise sur le site 
pour signifié que le site n'est pas encore prêt
*/
class BetaHTMLAdder
{
    // Méthode pour ajouter le " bêta " à une reponse
    public function addBeta(Response $response, $remainingDays)
    {
        $content = $response->getContent();

        /*
        $html = "<div style='position: absolute; top: 0; background: orange;
            width: 100%; text-align: center; padding: 0.5em;'> Beta J- {$remainingDays} ! </div>";
        */
        $html = "<div class='notification_beta'> Beta J- {$remainingDays} ! </div>";

        //Insertion du code dans la page, au début du <body>
        $content = str_replace('<body>', '<body>'.$html, $content);    

        $response->setContent($content);
        return $response;
    } 
}