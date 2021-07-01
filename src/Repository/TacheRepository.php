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

    public function recupereTousChronologiquement() {
        return $this->createQueryBuilder('t')
                ->orderBy('t.date_debut', 'DESC')
                ->getQuery()
                ->getResult();
    }

    public function recupereParPeriodeOuProjet($date_debut = "", $date_fin = "", $id_projet = "") {

        $qb = $this->createQueryBuilder('t');

        if($date_debut !== "") {
            $qb->where('t.date_debut >= :date_debut')
                ->setParameter('date_debut', $date_debut);
        }

        if($date_fin !== "") {
            $qb->andWhere('t.date_fin <= :date_fin')
                ->setParameter('date_fin', $date_fin);
        }

        if($id_projet !== "") {

            $qb->innerJoin('t.projet', 'p')
                ->andWhere('p.id = :id_projet')
                ->setParameter('id_projet', $id_projet);
        }

        return $qb->orderBy('t.date_debut', 'DESC')
                ->getQuery()
                ->getResult();
    }
}
