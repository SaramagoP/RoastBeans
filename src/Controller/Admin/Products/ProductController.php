<?php

namespace App\Controller\Admin\Products;


use App\Entity\Product;
use App\Form\AdminProductFormType;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin')]
class ProductController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em, 
        private ProductRepository $productRepository
    )
    {
    }

    #[Route('/product', name: 'admin_product_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('pages/admin/products/index.html.twig', [
            "produits" => $this->productRepository->findAll()
        ]);
    }

    #[Route('/product/create', name: 'admin_product_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        $product = new Product();

        $form = $this->createForm(AdminProductFormType::class, $product);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $this->em->persist($product);

            $this->em->flush();

            $this->addFlash('success', "Le produit a été ajoutée avec succès.");

            return $this->redirectToRoute('admin_product_index');
        }

        return $this->render('pages/admin/products/create.html.twig', [
            "form" => $form->createView()
        ]);
    }
}
