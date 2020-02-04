<?php

/* c'est un service */

namespace App\AntiSpam;

/*
c'est un service
*/
class OCAntiSpame
{
    /**
     *  Vérifié si le texte est un spam ou non
     * @param string $text
     * @return bool
     */
    public function isSpam($text)
    {
        //var_dump(strlen($text->getContent()));die;
        /*$text = (strlen($text) < 50) ? true : false;
        return $text;*/

        return (strlen($text->getContent()) < 50) ? true : false;
        //return !is_object($text);
    }
}