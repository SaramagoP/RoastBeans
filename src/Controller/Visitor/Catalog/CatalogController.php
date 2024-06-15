<?php

namespace App\Controller\Visitor\Catalog;

use App\Entity\Review;
use DateTimeImmutable;
use App\Entity\Product;
use App\Entity\Category;
use App\Form\ReviewFormType;
use App\Repository\ProductRepository;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/catalog')]
class CatalogController extends AbstractController
{
    public function __construct(
        private productRepository $productRepository)
    {
    }


    #[Route('/', name: 'visitor_catalog_index', methods: ['GET'])]
    public function index(CategoryRepository $categoryRepository, ProductRepository $productRepository): Response
    {
        // Récupération de toutes les catégories depuis le repository des catégories
        $categories = $categoryRepository->findAll();

        // Récupération de tous les produits depuis le repository des produits
        $products = $productRepository->findAll();

        // Affichage de la page d'index du catalogue avec la liste des produits et des catégories
        return $this->render('pages/visitor/catalog/index.html.twig', [
            "products" => $products,
            "categories" => $categories
        ]);
    }


    #[Route('/{id}/{slug}/show', name: 'visitor_catalog_product_show', methods: ['GET', 'POST'])]
    public function show(
        Product $product, 
        Request $request, 
        EntityManagerInterface $em,
    ): Response
    {
        // Création d'une nouvelle instance de Review pour le formulaire
        $reviews = new Review();

        // Création du formulaire de création d'une critique pour le produit actuel
        $form = $this->createForm(ReviewFormType::class, $reviews);

        // Traitement de la soumission du formulaire
        $form->handleRequest($request);

        if ( $form->isSubmitted() && $form->isValid() )
        {
            // Attribution de l'utilisateur actuellement connecté à la critique
            $reviews->setUser($this->getUser());

            // Attribution du produit actuel à la critique
            $reviews->setProduct($product);

            // Définition de la date de création de la critique
            $reviews->setCreatedAt(new DateTimeImmutable());

            // Persistance de la critique dans la base de données
            $em->persist($reviews);

            // Flush exécute les opérations en attente (ici, l'insertion de la critique)
            $em->flush(); // La méthode flush() est responsable de la synchronisation de toutes les opérations en attente avec la base de données.

            // Redirection vers la page de détails du produit après ajout de la critique
            return $this->redirectToRoute('visitor_catalog_product_show', [
                'id' => $product->getId(), 
                'slug' => $product->getSlug()
            ]);
        }

        return $this->render('pages/visitor/catalog/show.html.twig',[
            "product" => $product,
            "form" => $form->createView()
        ]);
    }


    #[Route('/product/filter-by-category/{id}/{slug}', name: 'visitor_filter_product_by_category', methods: ['GET'])]
    public function filterByCategory(
        Category $category, // Il s'agit d'un paramètre typé qui représente une instance de la classe Category.
        CategoryRepository $categoryRepository, // Il s'agit d'un service qui agit comme un pont entre l'application Symfony et la base de données pour la gestion des entités Category.
        ProductRepository $productRepository // Il s'agit d'un service qui agit comme un pont entre l'application Symfony et la base de données pour la gestion des entités Product.
    ): Response
    {
        // Récupère toutes les catégories pour afficher le menu de navigation
        $categories = $categoryRepository->findAll();

        // Récupère les produits filtrés par la catégorie spécifiée
        $products = $productRepository->filterProductsByCategory($category->getId());

        // Rend la vue Twig 'index.html.twig' avec les produits filtrés et toutes les catégories
        return $this->render('pages/visitor/catalog/index.html.twig', [
            "products" => $products,
            "categories" => $categories,
        ]);
    }
}
