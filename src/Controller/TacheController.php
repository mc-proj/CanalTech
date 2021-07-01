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

            if($donnees["date_debut"] !== "") {
                $donnees["date_debut"] = $donnees["date_debut"]->format('Y-m-d H:i:s');
                $donnees["date_fin"] = $donnees["date_fin"]->format('Y-m-d H:i:s');
            }

            $stats = $this->donneStats($donnees, $id_projet);

            return $this->render('tache/liste.html.twig', [
                'taches' => $taches,
                'stats' => $stats,
            ]);
        }

        if(count($taches) > 0) {

            $donnees = [
                "date_debut" => "",
                "date_fin" => ""
            ];
            $stats = $this->donneStats($donnees, "");
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

    private function donneStats($donnees, $id_projet) {

        $nombre_taches_effectuees = $this->tacheRepository->compteEffectuees($donnees["date_debut"], $donnees["date_fin"], $id_projet);
        $temps = $this->tacheRepository->recupereTotauxTemps($donnees["date_debut"], $donnees["date_fin"], $id_projet);
        $nombre_taches_effectuees = $nombre_taches_effectuees[0]["taches_effectuees"];
        $total_jours = $temps[0]["total_jours"];
        $total_heures = $temps[0]["total_heures"] - ($total_jours * 24);

        $nombre_jours_concernes = $total_jours;

        if($total_heures > 0) {
            $nombre_jours_concernes += 1;
        }

        if($nombre_jours_concernes > 0) {
            //le calcul concidere 1 journée de travail = 7h
            $heures_par_journee = 7;
            $temps_moyen_journalier = (($total_jours * $heures_par_journee) + $total_heures)/$nombre_jours_concernes;
            $temps_moyen_journalier = round($temps_moyen_journalier, 1);
        } else {
            $temps_moyen_journalier = 0;
        }
        

        return [
            "nombre de taches effectuees" => $nombre_taches_effectuees,
            "temps total taches" => $total_jours . " jours et " . $total_heures . " heures",
            "temps moyen par jour" => $temps_moyen_journalier . " heures"
        ];
    }
}