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


    public function compteEffectuees($date_debut = "", $date_fin = "", $id_projet = "") {
        $conn = $this->getEntityManager()->getConnection();
        $sql = '
            SELECT COUNT(*) AS taches_effectuees FROM tache t
            INNER JOIN projet
            ON t.projet_id = projet.id    
            WHERE t.date_fin < CURRENT_DATE()
        ';

        $options = $this->gestionFiltre($date_debut, $date_fin, $id_projet);
        $sql .= $options["suite_requete"];
        $stmt = $conn->prepare($sql);
        $stmt->execute($options["tableau_criteres"]);
        return $stmt->fetchAllAssociative();
    }

    public function recupereTotauxTemps($date_debut = "", $date_fin = "", $id_projet = "") {

        $conn = $this->getEntityManager()->getConnection();
        $sql = '     
            SELECT SUM(TIMESTAMPDIFF(hour, t.date_debut, t.date_fin)) AS total_heures,
            SUM(TIMESTAMPDIFF(day, t.date_debut, t.date_fin)) AS total_jours
            FROM tache t
            INNER JOIN projet
            ON t.projet_id = projet.id    
        ';

        $options = $this->gestionFiltre($date_debut, $date_fin, $id_projet);
        $sql .= $options["suite_requete"];
        $stmt = $conn->prepare($sql);
        $stmt->execute($options["tableau_criteres"]);
        return $stmt->fetchAllAssociative();
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

    private function gestionFiltre($date_debut, $date_fin, $id_projet) {

        $sql = '';
        $criteres_dates = array();
        $critere_projet = array();

        if($date_debut !== "") {
            $sql .= 'AND t.date_debut >= :date_debut
                    AND t.date_fin <= :date_fin
            ';

            $criteres_dates = ([
                'date_debut' => $date_debut,
                'date_fin' => $date_fin,
            ]);
        }

        if($id_projet !== "") {
            $sql .= 'AND t.projet_id = :id_projet';

            $critere_projet = ([
                'id_projet' => $id_projet
            ]);
        }

        $tableau_criteres = array_merge($criteres_dates, $critere_projet);

        return [
            "suite_requete" => $sql,
            "tableau_criteres" => $tableau_criteres
        ];
    }
}
