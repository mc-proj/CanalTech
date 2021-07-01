<?php

namespace App\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Projet;
use App\Repository\ProjetRepository;
use App\Form\ProjetType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
* @Route("/projet")
*/

class ProjetController extends AbstractController
{
    private $entityManager;
    private $projetRepository;

    public function __construct(EntityManagerInterface $entityManager,
                                ProjetRepository $projetRepository) {
        $this->entityManager = $entityManager;
        $this->projetRepository = $projetRepository;
    }

    /**
     * @Route("/", name="liste_projets", methods={"GET"})
     */
    public function liste(): Response
    {
        $projets = $this->projetRepository->findAll();
        return $this->render('projet/liste.html.twig', [
            'projets' => $projets
        ]);
    }

    /**
     * @Route("/nouveau", name="nouveau_projet", methods={"GET","POST"})
     */
    public function cree(Request $request) {

        $projet = new Projet();
        $form = $this->createForm(ProjetType::class, $projet);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($projet);
            $this->entityManager->flush();
            $this->addFlash('message_projet', 'Le nouveau projet a bien été enregistré');
            return $this->redirectToRoute('liste_projets');
        }

        return $this->render('projet/cree.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/edite/{id}", name="edite_projet", requirements={"id"="\d+"})
     */
    public function edite(Request $request, Projet $projet) {

        $form = $this->createForm(ProjetType::class, $projet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('message_projet', 'Le projet a bien été edité');
            return $this->redirectToRoute('liste_projets');
        }

        return $this->render('projet/edite.html.twig', [
            'projet' => $projet,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/supprime/{id}", name="supprime_projet", requirements={"id"="\d+"})
     */
    public function supprime(Projet $projet) {

        $this->entityManager->remove($projet);
        $this->entityManager->flush();
        $this->addFlash('message_projet', 'Le projet a bien été effacé');
        return $this->redirectToRoute('liste_projets');
    }
}