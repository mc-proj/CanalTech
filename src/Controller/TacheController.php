<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Tache;
use App\Repository\TacheRepository;
use App\Form\TacheType;
use App\Form\FiltreTacheType;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Services\StatsService;

/**
 * @Route("/")
 */

class TacheController extends AbstractController
{
    private $entityManager;
    private $tacheRepository;
    private $statsService;

    public function __construct(EntityManagerInterface $entityManager,
                                TacheRepository $tacheRepository,
                                StatsService $statsService) {
        $this->entityManager = $entityManager;
        $this->tacheRepository = $tacheRepository;
        $this->statsService = $statsService;
    }

    /**
     * @Route("/", name="accueil_taches", methods={"GET", "POST"})
     */
    public function index(Request $request) {
        $taches = $this->tacheRepository->findBy([], ["date_debut" => "DESC"]);
        $tache = new Tache();
        
        $form = $this->createForm(FiltreTacheType::class, $tache, [
            'attr' => [
                'id' => 'form-filtre'
            ],
        ]);

        $form->handleRequest($request);

        if($request->isMethod('POST')) {
            if($form->isSubmitted() && $form->isValid()) {
                $data = $form->getData();
                $projet_id = null;

                if($data->getProjet() !== null) {
                    $projet_id = $data->getProjet()->getId();
                }

                $taches = $this->tacheRepository->recupereParPeriodeOuProjet($data->getDateDebut(), $data->getDateFin(), $projet_id);
                $stats = $this->tacheRepository->getStats($data->getDateDebut(), $data->getDateFin(), $projet_id);
                $stats = $this->statsService->gereStats($stats);

                $response =  $this->render('tache/liste.html.twig', [
                    'taches' => $taches,
                    'stats' => $stats,
                ]);

                return $response;
            }
        }

        $stats = $this->tacheRepository->getStats(null, null, null);
        $stats = $this->statsService->gereStats($stats);

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
}