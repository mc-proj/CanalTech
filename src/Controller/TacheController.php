<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Tache;
use App\Entity\Projet;
use App\Repository\TacheRepository;
use App\Form\TacheType;
use App\Form\FiltreTacheType;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/")
 */

class TacheController extends AbstractController
{
    private $entityManager;
    private $tacheRepository;

    public function __construct(EntityManagerInterface $entityManager,
                                TacheRepository $tacheRepository) {
        $this->entityManager = $entityManager;
        $this->tacheRepository = $tacheRepository;
    }

    /**
     * @Route("/", name="accueil_taches", methods={"GET", "POST"})
     */
    public function index(Request $request) {
        
        $stats = 0;
        $taches = $this->tacheRepository->recupereTousChronologiquement();
        $tache = new Tache();
        $form = $this->createForm(FiltreTacheType::class, $tache);
        $form->handleRequest($request);

        if($request->isXmlHttpRequest()) {

            $donnees = json_decode($request->getContent(), true);
            $id_projet = $donnees["filtre_tache[projet]"];

            $donnees = $this->securiteFiltreForm($donnees);

            if($donnees["erreur"] !== null) {
                $response = new JsonResponse($donnees["erreur"]);
                return $response;
            }

            $taches = $this->tacheRepository->recupereParPeriodeOuProjet($donnees["date_debut"], $donnees["date_fin"], $id_projet);

            if(count($taches) > 0) {

                $stats = $this->donneStats($taches);
            }

            return $this->render('tache/liste.html.twig', [
                'taches' => $taches,
                'stats' => $stats,
            ]);
        }

        if(count($taches) > 0) {
            $stats = $this->donneStats($taches);
        }
        
        return $this->render('tache/accueil.html.twig', [
            'taches' => $taches,
            'form' => $form->createView(),
            'stats' => $stats,
        ]);
    }

    /**
     * @Route("/nouveau", name="nouvelle_tache", methods={"GET","POST"})
     */
    public function cree(Request $request) {

        $tache = new Tache();
        $form = $this->createForm(TacheType::class, $tache);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            $this->entityManager->persist($tache);
            $this->entityManager->flush();
            $this->addFlash('message_tache', 'La nouvelle tache a bien été enregistrée');
            return $this->redirectToRoute('accueil_taches');
        }

        return $this->render('tache/cree.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/edite/{id}", name="edite_tache", requirements={"id"="\d+"})
     */
    public function edite(Request $request, Tache $tache) {

        $form = $this->createForm(TacheType::class, $tache);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('message_tache', 'La tache a bien été editée');
            return $this->redirectToRoute('accueil_taches');
        }

        return $this->render('tache/edite.html.twig', [
            'tache' => $tache,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/supprime/{id}", name="supprime_tache", requirements={"id"="\d+"})
     */
    public function supprime(Tache $tache) {

        $this->entityManager->remove($tache);
        $this->entityManager->flush();
        $this->addFlash('message_tache', 'La tache a bien été effacée');
        return $this->redirectToRoute('accueil_taches');
    }

    /**
     * @Route("/facture", name="route_facture", methods={"POST"})
     */
    //marque une tache comme étant facturée
    public function Facture(Request $request) {
        if($request->isXmlHttpRequest()) {

            $retour = 0;
            $donnees = json_decode($request->getContent(), true);
            $tache = $this->tacheRepository->findOneBy(["id" => $donnees["id"]]);

            if($tache !== null) {
                $tache->setEstFacture(true);
                $this->entityManager->persist($tache);
                $this->entityManager->flush();
                $retour  =$donnees["id"];
            }

            $response = new JsonResponse($retour);
            return $response;
        }
    }

    private function securiteFiltreForm($donnees) {
        $date_debut = "";
        $date_fin = "";
        $erreur = null;

        //soit 2 dates sont fournies soit aucune
        if($donnees["filtre_tache[date_debut][date]"] === "" && $donnees["filtre_tache[date_fin][date]"] !== ""
            || $donnees["filtre_tache[date_debut][date]"] !== "" && $donnees["filtre_tache[date_fin][date]"] === "") {
                
            $erreur = "veuillez renseigner une date début et une date de fin";
        } else {  //a ce stade, on a soit 2 dates soit aucune
            //si 2 dates fournies
            if($donnees["filtre_tache[date_debut][date]"] !== "" && $donnees["filtre_tache[date_fin][date]"] !== "") {
                $date_debut = new DateTime($donnees["filtre_tache[date_debut][date]"] . 'T' . $donnees["filtre_tache[date_debut][time]"]);
                $date_fin = new DateTime($donnees["filtre_tache[date_fin][date]"] . 'T' . $donnees["filtre_tache[date_fin][time]"]);
            } else if($donnees["filtre_tache[date_debut][time]"] !== "" || $donnees["filtre_tache[date_fin][time]"] !== "") { //si aucune date fournie
                $erreur = "veuillez renseigner une date avec l'heure";
            }
        }

        //si 2 dates sont fournies
        if($date_debut !== "") {
            if($date_debut == $date_fin) {
                $erreur = "les dates de début et de fin sont identiques";
            } else if($date_debut > $date_fin) {
                $erreur = "la date de fin est antérieure à la date de début";
            }
        }

        return [
            "date_debut" => $date_debut,
            "date_fin" => $date_fin,
            "erreur" => $erreur
        ];
    }

    private function donneStats($taches) {

        $nombre_taches_effectuees = 0;
        $date_zero = new DateTime('00:00');
        $clone_date_zero = clone $date_zero;

        foreach($taches as $tache) {

            $debut = $tache->getDateDebut();
            $fin = $tache->getDateFin();

            if($fin < new \DateTime()) {
                $nombre_taches_effectuees += 1;
            }

            if($fin !== null) {
                $difference = $debut->diff($fin);
                $date_zero->add($difference);
            }
        }

        $duree_totale_taches = $clone_date_zero->diff($date_zero);
        $duree_totale = [
            "heures" => $duree_totale_taches->format('%h'),
            "jours" => $duree_totale_taches->format('%a'),
        ];

        $total_heures = $duree_totale["heures"] + $duree_totale["jours"] * 7;

        if($duree_totale["heures"] > 0) {
            $duree_totale["jours"] += 1;
        }

        $temps_moyen_journalier = $total_heures/$duree_totale["jours"];
        $temps_moyen_journalier = round($temps_moyen_journalier, 1);

        return [
            "nombre de taches effectuees" => $nombre_taches_effectuees,
            "temps total taches" => $duree_totale["jours"] . " jours et " . $duree_totale["heures"] . " heures",
            "temps moyen par jour" => $temps_moyen_journalier . " heures"
        ];
    }
}