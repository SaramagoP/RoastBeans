<?php

namespace App\Controller\Admin\Products;


use DateTimeImmutable; // Importation de la classe DateTimeImmutable pour gérer les dates immuables.
use App\Entity\Product; // Importation de la classe entité Product.
use App\Form\AdminProductFormType; // Importation de la classe de formulaire pour gérer les formulaires de produit.
use App\Repository\ProductRepository; // Importation de la classe de repository pour interroger les produits.
use App\Repository\CategoryRepository; // Importation de la classe de repository pour interroger les catégories.
use Doctrine\ORM\EntityManagerInterface; // Importation de l'interface EntityManagerInterface pour interagir avec la base de données.
use Symfony\Component\HttpFoundation\Request; // Importation de la classe Request pour gérer les requêtes HTTP.
use Symfony\Component\HttpFoundation\Response; // Importation de la classe Response pour envoyer des réponses HTTP.
use Symfony\Component\Routing\Attribute\Route; // Importation de l'attribut Route pour le routage.
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController; // Importation de la classe de contrôleur de base de Symfony.

#[Route('/admin')] // Définition de la route de base pour la section admin.
class ProductController extends AbstractController // Extension de la classe AbstractController pour utiliser les fonctionnalités communes du contrôleur.
{
    public function __construct(
        private EntityManagerInterface $em, // Injection du service EntityManagerInterface.
        private ProductRepository $productRepository, // Injection du service ProductRepository.
        private CategoryRepository $categoryRepository // Injection du service CategoryRepository.
    )
    {
    }

    #[Route('/product/list', name: 'admin_product_index', methods: ['GET'])] // Définition de la route pour lister les produits.
    public function index(): Response // Méthode pour gérer la liste des produits.
    {
        return $this->render('pages/admin/products/index.html.twig', [ // Rendu de la vue pour la liste des produits.
            "produits" => $this->productRepository->findAll() // Passage de tous les produits à la vue.
        ]);
    }

    #[Route('/product/create', name: 'admin_product_create', methods: ['GET', 'POST'])] // Définition de la route pour créer un produit.
    public function create(Request $request): Response // Méthode pour gérer la création d'un nouveau produit.
    {
        if (\count($this->categoryRepository->findAll()) == 0) // Vérification s'il existe au moins une catégorie avant de continuer.
        {
            $this->addFlash("warning", "Pour créer un produit, vous devez d'abord créer au moins une catégorie"); // Ajout d'un message flash d'avertissement.
            return $this->redirectToRoute('admin_category_index'); // Redirection vers la page d'index des catégories.
        }

        $product = new Product(); // Création d'un nouvel objet Product.

        $form = $this->createForm(AdminProductFormType::class, $product); // Création du formulaire pour le nouveau produit.

        $form->handleRequest($request); // Gestion de la requête avec le formulaire.

        if ($form->isSubmitted() && $form->isValid()) // Vérification si le formulaire est soumis et valide.
        {
            $product->setCreatedAt(new DateTimeImmutable()); // Définition de la date de création du produit.
            $product->setUpdatedAt(new DateTimeImmutable()); // Définition de la date de mise à jour du produit.

            $this->em->persist($product); // Persistance de l'entité produit.

            $this->em->flush(); // Validation des modifications dans la base de données.

            $this->addFlash('success', "Le produit a été ajouté avec succès."); // Ajout d'un message flash de succès.

            return $this->redirectToRoute('admin_product_index'); // Redirection vers la page d'index des produits.
        }

        return $this->render('pages/admin/products/create.html.twig', [ // Rendu de la vue pour la création de produit.
            "form" => $form->createView() // Passage de la vue du formulaire à la vue.
        ]);
    }

    #[Route('/product/{id<\d+>}/edit', name: 'admin_product_edit', methods: ['GET', 'POST'])] // Définition de la route pour modifier un produit.
    public function edit(Product $product, Request $request): Response // Méthode pour gérer la modification d'un produit existant.
    {
        // Vérification s'il existe au moins une catégorie avant de continuer.
        if (\count($this->categoryRepository->findAll()) == 0)
        {
            $this->addFlash("warning", "Pour modifier un produit, vous devez d'abord créer au moins une catégorie"); // Ajout d'un message flash d'avertissement.
            return $this->redirectToRoute('admin_category_index'); // Redirection vers la page d'index des catégories.
        }

        $form = $this->createForm(AdminProductFormType::class, $product); // Création du formulaire pour le produit existant.

        $form->handleRequest($request); // Gestion de la requête avec le formulaire.

        if ($form->isSubmitted() && $form->isValid()) // Vérification si le formulaire est soumis et valide.
        {
            $product->setCreatedAt(new DateTimeImmutable()); // Définition de la date de création du produit.
            $product->setUpdatedAt(new DateTimeImmutable()); // Définition de la date de mise à jour du produit.

            $this->em->persist($product); // Persistance de l'entité produit.

            $this->em->flush(); // Validation des modifications dans la base de données.

            $this->addFlash('success', "Le produit {$product->getName()} a été modifié avec succès."); // Ajout d'un message flash de succès.

            return $this->redirectToRoute('admin_product_index'); // Redirection vers la page d'index des produits.
        }

        return $this->render('pages/admin/products/edit.html.twig', [ // Rendu de la vue pour la modification de produit.
            "form" => $form->createView(), // Passage de la vue du formulaire à la vue.
            "product" => $product // Passage de l'objet produit à la vue.
        ]);
    }

    #[Route('/product/{id<\d+>}/delete', name: 'admin_product_delete', methods: ['DELETE'])] // Définition de la route pour supprimer un produit.
    public function delete(Product $product, Request $request): Response // Méthode pour gérer la suppression d'un produit.
    {
        if ($this->isCsrfTokenValid('delete_product_'.$product->getId(), $request->request->get('_csrf_token'))) // Vérification de la validité du token CSRF.
        // Explication détaillée
        // $this->isCsrfTokenValid(...) :

        // $this : fait référence à l'instance actuelle du contrôleur. isCsrfTokenValid est une méthode héritée de la classe AbstractController.
        // isCsrfTokenValid : cette méthode est utilisée pour vérifier si un jeton CSRF (Cross-Site Request Forgery) est valide. Le CSRF est une mesure de sécurité utilisée pour s'assurer que les requêtes à l'application proviennent de l'utilisateur authentifié et non d'un attaquant.
        // 'delete_product_'.$product->getId() :

        // C'est le jeton CSRF id.
        // 'delete_product_' : préfixe utilisé pour identifier l'action (ici, la suppression d'un produit).
        // $product->getId() : identifiant unique du produit à supprimer. Cela permet de générer un jeton unique pour chaque action de suppression de produit spécifique.
        // $request->request->get('_csrf_token') :

        // $request : instance de la classe Request qui contient les informations sur la requête HTTP actuelle.
        // request->get('_csrf_token') : accède aux données POST de la requête pour récupérer la valeur du champ _csrf_token. Ce champ doit être inclus dans le formulaire HTML envoyé par l'utilisateur lors de la requête de suppression.
        {
            $this->addFlash('success', "Le produit {$product->getName()} a été supprimé"); // Ajout d'un message flash de succès.

            $this->em->remove($product); // Suppression de l'entité produit.

            $this->em->flush(); // Validation des modifications dans la base de données.

            return $this->redirectToRoute('admin_product_index'); // Redirection vers la page d'index des produits.
        }

        return $this->redirectToRoute('admin_product_index'); // Redirection vers la page d'index des produits si le token CSRF n'est pas valide.
    }
}