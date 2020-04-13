<?php
// création de mon propre convertisseur

namespace App\ParamConverter;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
//use /ensio\Bundle\FrameworkExtraBundle\Request\Configuration\ParamConverterInterface;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;

class JsonParamConverter implements ParamConverterInterface
{
    /**
     * retourne true lorsque le convertisseur souhaite convertir le paramètre en question
     * false sinon
     */
    function supports(ParamConverter $configuration)
    {
        /*Si le nom de l'argument du controleur n'est pas "json",
        on n'applique pas le convertisseur */
        if('json' !== $configuration->getName())
        {
            return false;
        }
        return true;
    }

    /**
     * créer un attribut de requête, qui sera injecté dans l'argument de
     * la méthode du controleur 
     */
    function apply(Request $request, ParamConverter $configuration)
    {
        //on récupère le valeur actuelle de l'attribut
        $json = $request->attributes->get('json');

        //on effectue notre action : le décoder
        $json = json_decode($json, true);

        //on met à jour la nouvelle valeur de l'attribut
        $request->attributes->set('json', $json);

        return true;
    }
}