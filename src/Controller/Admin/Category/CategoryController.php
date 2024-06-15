<?php

namespace App\Controller\Admin\Category;

use DateTimeImmutable; // Importation de la classe DateTimeImmutable pour gérer les dates et heures immutables.
use App\Entity\Category; 
use App\Form\AdminCategoryFormType; // Importation de la classe de formulaire pour gérer les formulaires de catégorie.
use App\Repository\CategoryRepository; // Importation de la classe de repository pour interroger les catégories.
use Doctrine\ORM\EntityManagerInterface; // Importation de l'interface EntityManagerInterface pour interagir avec la base de données.
use Symfony\Component\HttpFoundation\Request; // Importation de la classe Request pour gérer les requêtes HTTP.
use Symfony\Component\HttpFoundation\Response; // Importation de la classe Response pour envoyer des réponses HTTP.
use Symfony\Component\Routing\Attribute\Route; // Importation de l'attribut Route pour le routage.
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController; // Importation de la classe de contrôleur de base de Symfony.


#[Route('/admin')] // Définition de la route de base pour la section admin.
class CategoryController extends AbstractController // il herite de la classe AbstractController pour utiliser les fonctionnalités communes du contrôleur.
{

    public function __construct(
        private EntityManagerInterface $em, // Injection du service EntityManagerInterface.
        private CategoryRepository $categoryRepository // Injection du service CategoryRepository, pour utiliser les methodes find.
    )
    {
    }

    #[Route('/category/list', name: 'admin_category_index', methods: ['GET'])] // Définition de la route pour lister les catégories.
    public function index(): Response // Méthode pour gérer la liste des catégories.
    {
        return $this->render('pages/admin/category/index.html.twig', [ // Rendu de la vue pour la liste des catégories.
            "categories" => $this->categoryRepository->findAll() // Passage de toutes les catégories à la vue.
        ]);
    }


    #[Route('/category/create', name: 'admin_category_create', methods: ['GET', 'POST'])] // Définition de la route pour créer une catégorie.
    public function create(Request $request): Response // Méthode pour gérer la création d'une nouvelle catégorie.
    {
        $category = new Category(); // Création d'un nouvel objet Category.

        $form = $this->createForm(AdminCategoryFormType::class, $category); // Création du formulaire pour la nouvelle catégorie.

        $form->handleRequest($request); //Associer les données de la requête aux données du formulaire

        if ($form->isSubmitted() && $form->isValid()) // Vérification si le formulaire est soumis et valide.
        {
            $category->setCreatedAt(new DateTimeImmutable()); // Définition de la date de création de la catégorie.
            $category->setUpdatedAt(new DateTimeImmutable()); // Définition de la date de mise à jour de la catégorie.

            $this->em->persist($category); //Demander au manager des entités de préparer la requête d'insertion de la nouvelle category en base de données

            $this->em->flush(); // Validation des modifications dans la base de données. Exécuter la requête

            $this->addFlash('success', "La catégorie a été ajoutée avec succès."); // Ajout d'un message flash de succès.

            return $this->redirectToRoute('admin_category_index'); // Redirection vers la page d'index des catégories.
        }

        return $this->render('pages/admin/category/create.html.twig', [ // Rendu de la vue pour la création de catégorie. Puis arrêter l'exécution du script
            "form" => $form->createView() // Passage de la vue du formulaire à la vue.
        ]);
    }


    #[Route('/category/{id<\d+>}/edit', name: 'admin_category_edit', methods: ['GET', 'POST'])] // Définition de la route pour modifier une catégorie.
    public function edit(Category $category, Request $request): Response // Méthode pour gérer la modification d'une catégorie existante.
    {
        $form = $this->createForm(AdminCategoryFormType::class, $category); // Création du formulaire pour la catégorie existante.

        $form->handleRequest($request); // Gestion de la requête avec le formulaire.

        if ($form->isSubmitted() && $form->isValid()) // Vérification si le formulaire est soumis et valide.
        {
            $category->setCreatedAt(new DateTimeImmutable()); // Définition de la date de création de la catégorie.
            $category->setUpdatedAt(new DateTimeImmutable()); // Définition de la date de mise à jour de la catégorie.

            $this->em->persist($category); // Persistance de l'entité catégorie.

            $this->em->flush(); // Validation des modifications dans la base de données.

            $this->addFlash('success', "La catégorie {$category->getName()} a été modifiée avec succès."); // Ajout d'un message flash de succès.

            return $this->redirectToRoute('admin_category_index'); // Redirection vers la page d'index des catégories. Puis arrêter l'exécution du script
        }

        return $this->render('pages/admin/category/edit.html.twig', [ // Rendu de la vue pour la modification de catégorie.
            "form" => $form->createView(), // Passage de la vue du formulaire à la vue.
            "category" => $category // Passage de l'objet catégorie à la vue.
        ]);
    }


    #[Route('/category/{id<\d+>}/delete', name: 'admin_category_delete', methods: ['DELETE'])] // Définition de la route pour supprimer une catégorie.
    public function delete(Category $category, Request $request): Response // Méthode pour gérer la suppression d'une catégorie.
    {
        if ( $this->isCsrfTokenValid('delete_category_'.$category->getId(), $request->request->get('_csrf_token')) ) // Si le jéton de sécurité pour se protéger contre les failles de type csrf est valide. Donc vérification de la validité du token CSRF.
        {
            $this->addFlash('success', "La catégorie {$category->getName()} a été supprimée avec succès."); // Ajout d'un message flash de succès.
            
            $this->em->remove($category); // Demander au manager des entités de préparer la requête de suppression de l'entité catégorie.
            
            $this->em->flush(); // Exécuter la requête, donc validation des modifications dans la base de données.
            

            return $this->redirectToRoute('admin_category_index'); // Redirection vers la page d'index des catégories. Puis arrêter l'exécution du script
        }
    }
}

