<?php

namespace App\AntiSpam;
//var_dump('ok 111');//die;

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
        //var_dump('ok pour isSpam');die;
        /*$text = (strlen($text) < 50) ? true : false;
        return $text;*/

        return !is_array($text);
    }
}
//dump(new OCAntiSpame);//die;
//var_dump('ok pour ');//die;