<?php

/* c'est un service */

namespace App\Purger;

use Doctrine\ORM\EntityManagerInterface;
use App\Repository\AdvertRepository;
use App\Repository\AdvertSkillRepository;


class OCPurger
{
    /** @var EntityManagerInterface */
    private $em;

    /** @var AdvertRepository */
    private $repoAdvert;

    /** @var AdvertSkillRepository */
    private $repoAdvertSkill;

    //on injecte l'entityMangager
    public function __construct(EntityManagerInterface $em, AdvertRepository $repoAdvert, AdvertSkillRepository $repoAdvertSkill )
    {
        $this->em               = $em;
        $this->repoAdvert       = $repoAdvert;
        $this->repoAdvertSkill = $repoAdvertSkill;
    }

    public function purge($days)
    {
        // date d'il y a X jours
        $date = new \DateTime($days . 'days ago');

        //on récupère les annonces à supprimer
        $listAdverts = $this->repoAdvert->getAdvertsBefore($date);

        //on parcourt les annonces pour les supprimer
        foreach($listAdverts as $advert){

            //On récupère les AdvertSkill liées à cette annonce
            $advertSkills = $this->repoAdvertSkill->findBy(['advert' => $advert]);

            //On supprime les advertSkill avant de supprimer les annonces
            foreach ($advertSkills as $advertSkill) {
                $this->em->remove($advertSkill);
            }

            // On peut supprimer l'annonces
            $this->em->remove($advert);
        }

        $this->em->flush();
    }
}