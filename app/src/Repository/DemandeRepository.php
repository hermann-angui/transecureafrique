<?php

namespace App\Repository;

use App\Entity\Demande;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use phpDocumentor\Reflection\Types\Collection;

/**
 * @extends ServiceEntityRepository<Demande>
 *
 * @method Demande|null find($id, $lockMode = null, $lockVersion = null)
 * @method Demande|null findOneBy(array $criteria, array $orderBy = null)
 * @method Demande[]    findAll()
 * @method Demande[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DemandeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry){
        parent::__construct($registry, Demande::class);
    }

    public function add(Demande $entity, bool $flush = false): void{
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Demande $entity, bool $flush = false): void{
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }


    public function findTotaNotPayed(){
        $result = $this->createQueryBuilder('d')
            ->select('COUNT(d)')
            ->where('d.status = :param' )
            ->andWhere("d.payment IS NULL")
            ->setParameter('param', 'WAITING_FOR_PAYMENT')
            ->getQuery();

        return $result->getSingleScalarResult();
    }

    public function findTotalUndeliveredEachMonth(){
        $result = $this->createQueryBuilder('d')
            ->select('MONTH(d.created_at) as mois_num, count(d) AS total')
            ->andWhere("d.payment IS NULL")
            ->groupBy('mois_num')
            ->getQuery();
        return $result->getResult();
    }

    public function findTotalDaily() {
        $now = new \DateTime('now');
        return $this->createQueryBuilder('d')
            ->select('COUNT(d)')
            ->where('d.created_at = :param' )
            ->setParameter('param', $now->format('Y-m-d'))
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function findTotalWeekly(){
        $start = date("Y-m-d 00:00:00", strtotime('monday this week'));
        $end = date("Y-m-d 00:00:00", strtotime('sunday this week'));
        $result = $this->createQueryBuilder('d')
                    ->select('COUNT(d)')
                    ->andWhere("d.created_at BETWEEN :start AND :end")
                    ->setParameter('start', $start)
                    ->setParameter('end', $end)
                    ->getQuery();

        return $result->getSingleScalarResult();
    }

    public function findGroupByMarque(){
        $result = $this->createQueryBuilder('d')
            ->select('count(d.marque_du_vehicule) as value, d.marque_du_vehicule as name')
            ->andWhere('d.payment IS NOT NULL')
            ->groupBy('d.marque_du_vehicule')
            ->getQuery();
        return $result->getResult();
    }

    public function findGroupByEnergie(){
        $result = $this->createQueryBuilder('d')
            ->select('count(d.energie_vehicule) as value, d.energie_vehicule as name')
            ->andWhere("d.payment IS NOT NULL")
            ->groupBy('d.energie_vehicule')
            ->getQuery();
        return $result->getResult();
    }
}
