<?php

/* Ce mini service nous permettra d'utiliser la fonction {{ checkIfSpam("mon message") }} , 
dans twig , grace au tag
On va configurer le services.yaml mais sur symfony 4 , on a pas besoin de configure le services.yaml

Twig propose une classe abstraite à hériter par notre service, il s'agit de AbstractExtension

Cette section est propre à chaque tag, où celui qui récupère les services d'un certain tag va exécuter 
telle ou telle méthode des services tagués. En l'occurrence, Twig va exécuter les méthodes suivantes :

getFilters(), qui retourne un tableau contenant les filtres que le service ajoute à Twig ;

getTests(), qui retourne les tests ;

getFunctions(), qui retourne les fonctions ;

getOperators(), qui retourne les opérateurs.

Pour notre exemple, nous allons juste ajouter une fonction accessible dans nos vues via{{ checkIfSpam('le message') }}. 
Elle vérifie si son argument est un spam. Pour cela, écrivons la méthode getFunctions() suivante dans notre service :
*/

namespace App\Twig;

use App\AntiSpam\OCAntiSpame;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AntispamExtension extends AbstractExtension
{
    /**
     * @var OCAntispam
     */
    private $ocAntispam;

    public function __construct(OCAntiSpame $ocAntispam)
    {
        $this->ocAntispam = $ocAntispam;
    }

    public function checkIfArgumentIsSpam($text)
    {
        return $this->ocAntispam->isSpam($text);
    }

    /**
     * sur twig , {{ checkIfSpam(var) }} sera en mode fonction
     */
    public function getFunctions()
    {
        /*
        checkIfSpam est le nom de la fonction qui sera disponible dans nos vues Twig
        [$this, 'checkIfArgumentIsSpam'] callable, c'est comme si on faisait [$this->ocAntispam, 'isSpam']
        Au final, {{ checkIfSpam(var) }} côté Twig exécute $this->isSpam($var) côté OCAntiSpam 
        */
        return [
            new TwigFunction('checkIfSpam', [$this, 'checkIfArgumentIsSpam']),
        ];
    }

    public function getName()
    {
        return 'OCAntiSpame';
    }

    /**
     * sur twig , {{ var | checkIfSpam }} sera en mode filtre
     */
    public function getFilters()
    {
        return [
            new TwigFunction('checkIfSpam', [$this, 'checkIfArgumentIsSpam']),
        ];
    }
}