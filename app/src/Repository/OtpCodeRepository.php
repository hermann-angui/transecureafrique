<?php

namespace App\Repository;

use App\Entity\OtpCode;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<OtpCode>
 *
 * @method OtpCode|null find($id, $lockMode = null, $lockVersion = null)
 * @method OtpCode|null findOneBy(array $criteria, array $orderBy = null)
 * @method OtpCode[]    findAll()
 * @method OtpCode[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OtpCodeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OtpCode::class);
    }

    public function add(OtpCode $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(OtpCode $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
