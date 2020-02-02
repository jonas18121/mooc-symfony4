<?php

namespace App\Repository;

use App\Entity\Advert;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Advert|null find($id, $lockMode = null, $lockVersion = null)
 * @method Advert|null findOneBy(array $criteria, array $orderBy = null)
 * @method Advert[]    findAll()
 * @method Advert[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AdvertRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Advert::class);
    }

    public function getAdvertWithApplication()
    {
        $qb = $this->createQueryBuilder('a') // on crée une select('a')
                   ->leftJoin('a.applications', 'app') // on crée une jointure avec leftJoin() ou innerJoin()
                   ->addSelect('app'); // on selectionne l'entité jointe grace a son alias 'app'

        //on retourne le résultat           
        return $qb->getQuery()
                  ->getResult();
    }

    /**
     * récupère toutes les annonces qui correspondent à une liste de catégories
     */
    public function getAdvertWithCategories(array $categoryNames)
    {
        $qb = $this->createQueryBuilder('a') // on crée une select('a')
                   ->innerJoin('a.categories', 'c') // on crée une jointure avec innerJoin() et on crée son alias qui sera 'c'
                   ->addSelect('c'); // on selectionne l'entité jointe grace a son alias 'c'
                   
        // puis on filtre sur le nom des catégories à l'aide d'un IN           
        $qb->where($qb->expr()->in('c.name', $categoryNames));

        /* expr() permet de créer une expression qui sera traduite comme ça " WHERE c.name IN [...]
            pour une expr(), il y a des : IN, LIKE, ORX
        " */

        //on retourne le résultat           
        return $qb->getQuery()
                  ->getResult();
    }

    

    /*
    public function whereCurrentYear(QueryBuilder $queryBuilder)
    {
        $queryBuilder->andWhere('a.date BETWEEN :start AND :end')
                     ->setParameter('start', new \DateTime(date('Y').'-01-01'))// date du 1er janvier de cette année
                     ->setParameter('end', new \DateTime(date('Y').'-12-31'));// date du 31 Décembre de cette année
    }*/

    // /**
    //  * @return Advert[] Returns an array of Advert objects
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
    public function findOneBySomeField($value): ?Advert
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
