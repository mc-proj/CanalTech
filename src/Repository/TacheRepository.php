<?php

namespace App\Repository;
use App\Entity\Tache;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Tache|null find($id, $lockMode = null, $lockVersion = null)
 * @method Tache|null findOneBy(array $criteria, array $orderBy = null)
 * @method Tache[]    findAll()
 * @method Tache[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TacheRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tache::class);
    }

    public function recupereParPeriodeOuProjet($date_debut, $date_fin, $id_projet) {

        $qb = $this->createQueryBuilder('t');

        if($date_debut !== null) {
            $qb->where('t.date_debut >= :date_debut')
                ->setParameter('date_debut', $date_debut);
        }

        if($date_fin !== null) {
            $qb->andWhere('t.date_fin <= :date_fin')
                ->setParameter('date_fin', $date_fin);
        }

        if($id_projet !== null) {

            $qb->innerJoin('t.projet', 'p')
                ->andWhere('p.id = :id_projet')
                ->setParameter('id_projet', $id_projet);
        }

        return $qb->orderBy('t.date_debut', 'DESC')
                ->getQuery()
                ->getResult();
    }

    public function getStats($date_debut, $date_fin, $id_projet) {
        $qb = $this->createQueryBuilder('t');
        $qb->select('SUM(TIMESTAMPDIFF(day, t.date_debut, t.date_fin)) as total_jours');
        $qb->addSelect('SUM(TIMESTAMPDIFF(hour, t.date_debut, t.date_fin)) as total_heures');
        $qb->addSelect('COUNT(CASE WHEN t.date_fin > CURRENT_DATE() THEN 1 ELSE 0 END) as nombre_finies');

        if($date_debut !== null) {
            $qb->andWhere('t.date_debut >= :date_debut')
                ->setParameter('date_debut', $date_debut);
        }

        if($date_fin !== null) {
            $qb->andWhere('t.date_fin <= :date_fin')
                ->setParameter('date_fin', $date_fin);
        }

        if($id_projet !== null) {

            $qb->innerJoin('t.projet', 'p')
                ->andWhere('p.id = :id_projet')
                ->setParameter('id_projet', $id_projet);
        }

        return $qb->getQuery()
                ->getSingleResult();
    }
}