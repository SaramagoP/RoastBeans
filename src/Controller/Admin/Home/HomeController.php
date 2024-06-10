<?php

namespace App\Controller\Admin\Home;


use App\Entity\User;
use App\Entity\Order;
use App\Entity\Review;
use App\Entity\Contact;
use App\Entity\Product;
use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin')] // Définition de la route de base pour la section admin.
class HomeController extends AbstractController // Extension de la classe AbstractController pour utiliser les fonctionnalités communes du contrôleur.
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/home', name: 'admin_home', methods: ['GET'])]
    public function index(): Response 
    {
        $produitsCount = $this->entityManager->getRepository(Product::class)->count([]);
        $categoriesCount = $this->entityManager->getRepository(Category::class)->count([]);
        $commandesCount = $this->entityManager->getRepository(Order::class)->count([]);
        $utilisateursCount = $this->entityManager->getRepository(User::class)->count([]);
        $contactsCount = $this->entityManager->getRepository(Contact::class)->count([]);
        $reviewsCount = $this->entityManager->getRepository(Review::class)->count([]);

        return $this->render('pages/admin/home/index.html.twig', [
            'produitsCount' => $produitsCount,
            'categoriesCount' => $categoriesCount,
            'commandesCount' => $commandesCount,
            'utilisateursCount' => $utilisateursCount,
            'contactsCount' => $contactsCount,
            'reviewsCount' => $reviewsCount
        ]);
    }
}
