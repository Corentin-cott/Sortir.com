<?php

namespace App\Repository;

use App\Entity\Sortie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Sortie>
 */
class SortieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sortie::class);
    }

//    /**
//     * @return Sortie[] Returns an array of Sortie objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('s.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Sortie
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }


    public function findWithFilters($siteId, $search, $dateDebut, $dateFin, $user, $organisateur, $inscrit, $nonInscrit, $passees)
    {
        $qb = $this->createQueryBuilder('s')
            ->leftJoin('s.siteOrg', 'site')
            ->addSelect('site');

        if ($siteId) {
            $qb->andWhere('site.id = :siteId')
                ->setParameter('siteId', $siteId);
        }

        if ($search) {
            $qb->andWhere('s.nom LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        if ($dateDebut) {
            $qb->andWhere('s.dateHeureDebut >= :dateDebut')
                ->setParameter('dateDebut', $dateDebut);
        }

        if ($dateFin) {
            $qb->andWhere('s.dateHeureDebut <= :dateFin')
                ->setParameter('dateFin', $dateFin);
        }

        if ($organisateur && $user) {
            $qb->andWhere('s.organisateur = :user')
                ->setParameter('user', $user);
        }

        if ($inscrit && $user) {
            $qb->andWhere(':user MEMBER OF s.participants')
                ->setParameter('user', $user);
        }

        if ($nonInscrit && $user) {
            $qb->andWhere(':user NOT MEMBER OF s.participants')
                ->setParameter('user', $user);
        }

        if ($passees) {
            $qb->andWhere('s.dateHeureDebut < :today')
                ->setParameter('today', new \DateTime());
        }

        return $qb->getQuery()->getResult();
    }

}
