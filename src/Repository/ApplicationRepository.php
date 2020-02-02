<?php

namespace App\Repository;

use App\Entity\Application;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Application|null find($id, $lockMode = null, $lockVersion = null)
 * @method Application|null findOneBy(array $criteria, array $orderBy = null)
 * @method Application[]    findAll()
 * @method Application[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ApplicationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Application::class);
    }

    /**
     * récupère les X dernières candidatures avec leurs annonces associer
     */
    public function getApplicationsWithAdvert($limit)
    {
        $qb = $this->createQueryBuilder('a') // on crée une select('a')
                   ->innerJoin('a.advert', 'adv') // on crée une jointure avec innerJoin() et on crée son alias qui sera 'adv'
                   ->addSelect('adv'); // on selectionne l'entité jointe grace a son alias 'adv'

        //puis on retourne que $limit résultats
        $qb->setMaxResults($limit);


        //on retourne le résultat           
        return $qb->getQuery()
                  ->getResult();

    }


    // /**
    //  * @return Application[] Returns an array of Application objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Application
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
