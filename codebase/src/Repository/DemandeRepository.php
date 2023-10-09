<?php

namespace App\Repository;

use App\Entity\Demande;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

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

    public function findTotalNotPayed(){
        return $this->createQueryBuilder('d')
            ->select('COUNT(d)')
            ->where("d.payment IS NULL")
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function findTotalUndeliveredEachMonth() {
        return $this->createQueryBuilder('d')
            ->select('MONTH(d.created_at) as mois_num, count(d) AS total')
            ->where("d.payment IS NOT NULL")
            ->andWhere("d.status != 'CLOSED'")
            ->groupBy('mois_num')
            ->getQuery()
            ->getResult();
    }

    public function findTotalDaily() {
        $now = new \DateTime('now');

        return $this->createQueryBuilder('d')
            ->select('COUNT(d)')
            ->where('d.created_at = :param')
            ->andWhere("d.payment IS NOT NULL")
            ->setParameter('param', $now->format('Y-m-d'))
            ->getQuery()
            ->getSingleScalarResult()
            ;
    }

    public function findTotalWeekly(){
        $start = date("Y-m-d 00:00:00", strtotime('monday this week'));
        $end = date("Y-m-d 00:00:00", strtotime('sunday this week'));

        return $this->createQueryBuilder('d')
            ->select('COUNT(d)')
            ->where("d.payment IS NULL")
            ->andWhere("d.created_at BETWEEN :start AND :end")
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->getQuery()
            ->getSingleScalarResult()
            ;
    }

    public function findGroupByMarque(){
        return $this->createQueryBuilder('d')
            ->select('count(d.marque_du_vehicule) as value, d.marque_du_vehicule as name')
            ->andWhere('d.payment IS NOT NULL')
            ->groupBy('d.marque_du_vehicule')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findGroupByEnergie(){
        return $this->createQueryBuilder('d')
            ->select('count(d.energie_vehicule) as value, d.energie_vehicule as name')
            ->andWhere("d.payment IS NOT NULL")
            ->groupBy('d.energie_vehicule')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findTotalDemandePayed(){
        return $this->createQueryBuilder('d')
            ->select('count(d) as total')
            ->andWhere("d.payment IS NOT NULL")
            ->getQuery()
            ->getSingleScalarResult()
            ;
    }

    public function findOneByNumeroRecepisseOrNumeroVinChassis($numero_recepisse, $numero_vin_chassis){
        return $this->createQueryBuilder('d')
            ->where('d.numero_recepisse = :numero_recepisse')
            ->orWhere('d.numero_vin_chassis = :numero_vin_chassis')
            ->setParameter("numero_recepisse", $numero_recepisse)
            ->setParameter("numero_vin_chassis", $numero_vin_chassis)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findOneByNumeroCarteGriseOrNumeroImmatriculationOrNumeroVinChassis($numero_carte_grise, $numero_immatriculation, $numero_vin_chassis){
        return $this->createQueryBuilder('d')
            ->where('d.numero_carte_grise = :numero_carte_grise')
            ->orWhere('d.numero_immatriculation = :numero_immatriculation')
            ->orWhere('d.numero_vin_chassis = :numero_vin_chassis')
            ->setParameter("numero_carte_grise", $numero_carte_grise)
            ->setParameter("numero_immatriculation", $numero_immatriculation)
            ->setParameter("numero_vin_chassis", $numero_vin_chassis)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }
}
