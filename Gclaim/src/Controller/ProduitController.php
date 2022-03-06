<?php

namespace App\Controller;

use App\Entity\Images;
use App\Entity\Produit;
use App\Form\ProduitType;
use App\Repository\CategorieRepository;
use App\Repository\CommandeRepository;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/produit")
 */
class ProduitController extends AbstractController
{
    /**
     * @Route("/", name="produit_index", methods={"GET"})
     */
    public function index(ProduitRepository $produitRepository): Response
    {
        return $this->render('produit/index.html.twig', [
            'produits' => $produitRepository->findAll(),
            'user' => $this->getUser(),

        ]);
    }

    /**
     * @Route("/new", name="produit_new", methods={"GET", "POST"})
     */
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $produit = new Produit();
        $datetime = new \DateTime('now');
        $produit->setDateAjoutProduit($datetime);
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $images = $form ->get('images')->getData();
            foreach ($images as $image) {
                $fichier = md5(uniqid()) . '.' . $image->guessExtension();
                $image->move(
                    $this->getParameter('upload_directory'),
                    $fichier

                );

                $img = new Images();
                $img->setUrlImage($fichier);
                $produit-> addImage($img);
            }
            $entityManager->persist($img);
            $entityManager->persist($produit);
            $entityManager->flush();

            return $this->redirectToRoute('produit_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('produit/new.html.twig', [
            'produit' => $produit,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id_produit}", name="produit_show", methods={"GET"})
     */
    public function show(Produit $produit): Response
    {
        return $this->render('produit/show.html.twig', [
            'produit' => $produit,
        ]);
    }

    /**
     * @Route("/{id_produit}/edit", name="produit_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Produit $produit, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $images = $form ->get('images')->getData();
            foreach ($images as $image) {
                $fichier = md5(uniqid()) . '.' . $image->guessExtension();
                $image->move(
                    $this->getParameter('upload_directory'),
                    $fichier

                );

                $img = new Images();
                $img->setUrlImage($fichier);
                $produit-> addImage($img);
            }

            $entityManager->persist($img);
            $entityManager->persist($produit);
            $entityManager->flush();

            return $this->redirectToRoute('produit_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('produit/edit.html.twig', [
            'produit' => $produit,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id_produit}", name="produit_delete", methods={"POST"})
     */
    public function delete(Request $request, Produit $produit, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$produit->getId_produit(), $request->request->get('_token'))) {
            $entityManager->remove($produit);
            $entityManager->flush();
            
        }

        return $this->redirectToRoute('produit_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * @Route("/prod/frontp", name="produit", methods={"GET"})
     */
    public function indexFront(ProduitRepository $produitRepository, CategorieRepository $categorieRepository): Response
    {
        return $this->render('produit/indexFrontP.html.twig', [
            'produits' => $produitRepository->findAll(),
            'categories' => $categorieRepository->findAll()
            ,'user'=>$this->getUser(),
        ]);
    }
    /**
     * @Route("/prod/filtreprod", name="filtre", methods={"GET", "POST"})
     */
    public function filtreprod(ProduitRepository $produitRepository, CategorieRepository $categorieRepository, Request $request): Response
    {
        $cat = $request->get('cat');



        $products = $this->getDoctrine()
        ->getManager()
        ->createQuery('SELECT p FROM App\Entity\Produit p  WHERE p.categorie in (:list) ')
        ->setParameter('list',$cat)
        ->getResult();
        return $this->render('produit/indexFrontP.html.twig', [
            'produits' => $products,
            'categories' => $categorieRepository->findAll(),
        ]);

    }

}