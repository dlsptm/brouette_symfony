<?php

namespace App\Repository;

use App\Entity\CouponsType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CouponsType>
 *
 * @method CouponsType|null find($id, $lockMode = null, $lockVersion = null)
 * @method CouponsType|null findOneBy(array $criteria, array $orderBy = null)
 * @method CouponsType[]    findAll()
 * @method CouponsType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CouponsTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CouponsType::class);
    }

//    /**
//     * @return CouponsType[] Returns an array of CouponsType objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?CouponsType
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
