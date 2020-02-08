<?php

namespace App\Repository;

use App\Entity\Advert;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

use Doctrine\ORM\Tools\Pagination\Paginator;

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

    /**
     * récupérer les annonces de plus de X jours
     */
    public function getAdvertsBefore(\DateTime $date)
    {
        $query = $this->createQueryBuilder('a')
                      ->where('a.updatedAt <= :date')                       // Date de modification antérieure à :date
                      ->orWhere('a.updatedAt IS NULL AND a.date <= :date')  // Si la date de modification est vide, on vérifie la date de création
                      ->andWhere('a.applications IS EMPTY')                 // On vérifie que l'annonce ne contient aucune candidature
                      ->setParameter('date', $date)
                      ->getQuery()
        ;

        return $query->getResult();
    }


    /**
     * récupérer les annonces triées par date 
     */
    public function getAdverts($page, $nbPerPage)
    {
        $query = $this->createQueryBuilder('a') // on crée une select('a')

                      //jointure sur l'attribut image
                      ->leftJoin('a.image', 'i')
                      ->addSelect('i')

                      //jointure sur l'attribut categories
                      ->leftJoin('a.categories', 'c')
                      ->addSelect('c')

                      //trie par date en ordre décroissante
                      ->orderBy('a.date', 'DESC')

                      ->getQuery()
        ; 
        
        //dump(($page-1)* $nbPerPage);die;
        //on définit l'annonce à partir de laquelle commencer la liste
        $query->setFirstResult(($page-1)* $nbPerPage)

              //Ainsi que le nombre d'annonce à afficher sur une page
              ->setMaxResults($nbPerPage)
        ;

        //on retourne l'objet Paginator
        return new Paginator($query, true);
        
    }


    /**
     * récupère toutes les candidature qui correspondent à une liste d'annonces
     */
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
