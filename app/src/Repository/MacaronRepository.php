<?php

namespace App\Repository;

use App\Entity\Macaron;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Macaron>
 *
 * @method Macaron|null find($id, $lockMode = null, $lockVersion = null)
 * @method Macaron|null findOneBy(array $criteria, array $orderBy = null)
 * @method Macaron[]    findAll()
 * @method Macaron[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MacaronRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Macaron::class);
    }

    public function add(Macaron $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Macaron $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }


//    public function findOneBySomeField($value): ?Member
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

}
