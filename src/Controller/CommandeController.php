<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Form\CommandeType;
use App\Repository\CommandeRepository;
use App\Repository\ProduitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class CommandeController extends AbstractController
{
    /**
     * @Route("/commandes", name="commandes")
     */
    public function index(SessionInterface $session, ProduitRepository $produitRepository): Response
    {
        $commande = $session->get('commande', []);
        $commandeDetaillee = [];

        foreach ($commande as $id => $quantite) {
            $commandeDetaillee[] = ['produit' => $produitRepository->find($id), 'quantite' => $quantite];
        }

        $total = 0;
        foreach ($commandeDetaillee as $element) {
            $total += $element['produit']->getPrix() * $element['quantite'];
        }

        return $this->render('commande/index.html.twig', ['elements' => $commandeDetaillee, 'total' => $total, 'title' => 'Gestion Commande']);
    }

    /**
     * @Route("/commandes/create/{id}", name="create_commande")
     */
    public function create(int $id, SessionInterface $session): Response
    {
        $commande = $session->get('commande', []);

        if (!empty($commande[$id])) {
            $commande[$id]++;
        } else {
            $commande[$id] = 1;
        }

        $session->set('commande', $commande);

        return $this->redirectToRoute('produits', ['title' => 'Gestion Commande']);
    }

    /**
     * @Route("/commandes/store", name="store_commande")
     */
    public function store(Request $request): Response
    {
        $commande = new Commande();
        $form = $this->createForm(CommandeType::class, $commande);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()){
            $commande = $form->getData();

            $commande->setCreeLe(new \DateTime('now'));
            $commande->setModifieLe(new \DateTime('now'));

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($commande);
            $entityManager->flush();

            return $this->redirectToRoute('produits');
        }

        return $this->redirectToRoute('commandes');
    }

    /**
     * @Route("/commandes/remove/{id}", name="remove_commande")
     */
    public function remove(int $id, SessionInterface $session)
    {
        $commande = $session->get('commande', []);

        if (!empty($commande[$id])) {
            unset($commande[$id]);
        }

        $session->set('commande', $commande);

        return $this->redirectToRoute('commandes');
    }

    /**
     * @Route("/commandes/delete/{id}", name="delete_commande")
     */
    public function delete(CommandeRepository $commandeRepository, int $id): Response
    {
        $commande = $commandeRepository->find($id);

        if (!$commande) {
            throw $this->createNotFoundException(
                'No order found for id '.$id
            );
        }
        
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($commande);
        $entityManager->flush();

        return $this->redirectToRoute("commandes");
    }
}
