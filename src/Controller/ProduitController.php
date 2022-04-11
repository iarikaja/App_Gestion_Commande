<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Form\ProduitType;
use App\Repository\ProduitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
// use Symfony\Component\HttpFoundation\File\File;

class ProduitController extends AbstractController
{
    /**
     * @Route("/", name="produits")
     */
    public function index(ProduitRepository $produitRepository): Response
    {
        return $this->render('produit/index.html.twig', ['produits' =>$produitRepository->findAll(), 'title' => 'Metsena']);
    }

    /**
     * @Route("/create/produit", name="create_produit")
     */
    public function create(Request $request): Response
    {
        $produit = new Produit();
        $form = $this->createForm(ProduitType::class, $produit);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()){
            $produit = $form->getData();

            $photoFile = $form->get('photo')->getData();

            if ($photoFile) {
                $originalFilemame = pathinfo($photoFile->getClientOriginalName(), PATHINFO_FILENAME);
                $newFilename = $originalFilemame.'-'.uniqid().'.'.$photoFile->guessExtension();

                try {
                    $photoFile->move(
                        $this->getParameter('photos'),
                        $newFilename
                    );
                } catch (FileException $p){

                }

                $produit->setPhoto($newFilename);
            }

            $produit->setCreeLe(new \DateTime('now'));
            $produit->setModifieLe(new \DateTime('now'));

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($produit);
            $entityManager->flush();

            return $this->redirectToRoute('produits');
        }

        return $this->render('produit/create.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/show/produit/{id}", name="show_produit")
     */
    public function show(ProduitRepository $produitRepository, int $id): Response
    {
         $produit = $produitRepository
            ->find($id);
        
        if (!$produit) {
            throw $this->createNotFoundException('No produit found for id'.$id);
        }

        return $this->render('produit/show.html.twig', ['produit' => $produit]);
    }

     /**
      * @Route("/update/produit/{id}", name="update_produit")
      */
    public function update(ProduitRepository $produitRepository, Request $request, int $id): Response
    {
        $produit = $produitRepository->find($id);

        if (!$produit) {
            throw $this->createNotFoundException(
                'No product found for id '.$id
            );
        }

        // $produit->setPhoto(new File($this->getParameter('photos').'/'.$produit->getPhoto()));
          
        $form = $this->createForm(ProduitType::class, $produit);
        
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()){
            $produit = $form->getData();
            $photoFile = $form->get('photo')->getData();

            if ($photoFile) {
                $originalFilemame = pathinfo($photoFile->getClientOriginalName(), PATHINFO_FILENAME);
                $newFilename = $originalFilemame.'-'.uniqid().'.'.$photoFile->guessExtension();

                try {
                    $photoFile->move(
                        $this->getParameter('photos'),
                        $newFilename
                    );
                } catch (FileException $p){

                }

                $produit->setPhoto($newFilename);
            }

            $produit->setModifieLe(new \DateTime('now'));

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->flush();

            return $this->redirectToRoute('show_produit', ['id' => $produit->getId()]);
        }
        
        return $this->render('produit/update.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/delete/produit/{id}", name="delete_produit")
     */
    public function delete(ProduitRepository $produitRepository, int $id): Response
    {
        $produit = $produitRepository->find($id);

        if (!$produit) {
            throw $this->createNotFoundException(
                'No product found for id '.$id
            );
        }

        unlink(__DIR__ . '/public/uploads/photos/' . $produit->getPhoto());
        
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($produit);
        $entityManager->flush();

        return $this->redirectToRoute("produits");
    }

    /**
     * @Route("/search", name="search_produit")
     */
    public function search(ProduitRepository $produitRepository)
    {
        $query = explode('=', $_SERVER['QUERY_STRING'])[1];
        $produits = $produitRepository->findLike($query);

        // if (!$produits) {
        //     throw $this->createNotFoundException(
        //         'No product found for name '. $query
        //     );
        // }

        return $this->render("produit/search.html.twig", ['produits' => $produits, 'title' => 'Metsena']);
    }
}
