<?php

namespace App\Repository;

use App\Entity\ListeElectorale;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ListeElectorale>
 *
 * @method ListeElectorale|null find($id, $lockMode = null, $lockVersion = null)
 * @method ListeElectorale|null findOneBy(array $criteria, array $orderBy = null)
 * @method ListeElectorale[]    findAll()
 * @method ListeElectorale[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ListeElectoraleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ListeElectorale::class);
    }

    //    /**
    //     * @return ListeElectorale[] Returns an array of ListeElectorale objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('l')
    //            ->andWhere('l.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('l.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?ListeElectorale
    //    {
    //        return $this->createQueryBuilder('l')
    //            ->andWhere('l.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
