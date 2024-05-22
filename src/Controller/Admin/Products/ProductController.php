<?php

namespace App\Controller\Admin\Products;


use DateTimeImmutable;
use App\Entity\Product;
use App\Form\AdminProductFormType;
use App\Repository\ProductRepository;
use App\Repository\CategoryRepository;
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
        private ProductRepository $productRepository,
        private CategoryRepository $categoryRepository
    )
    {
    }

    #[Route('/product/list', name: 'admin_product_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('pages/admin/products/index.html.twig', [
            "produits" => $this->productRepository->findAll()
        ]);
    }

    #[Route('/product/create', name: 'admin_product_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        if ( \count($this->categoryRepository->findAll()) == 0 )
        {
            $this->addFlash("warning", "Pour créer un produit, vous devez d'abord créer au moins une catégorie");
            return $this->redirectToRoute('admin_category_index');
        }

        $product = new Product();

        $form = $this->createForm(AdminProductFormType::class, $product);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $product->setCreatedAt(new DateTimeImmutable());
            $product->setUpdatedAt(new DateTimeImmutable());

            $this->em->persist($product);

            $this->em->flush();

            $this->addFlash('success', "Le produit a été ajoutée avec succès.");

            return $this->redirectToRoute('admin_product_index');
        }

        return $this->render('pages/admin/products/create.html.twig', [
            "form" => $form->createView()
        ]);
    }


    #[Route('/product/{id<\d+>}/edit', name: 'admin_product_edit', methods: ['GET', 'POST'])]
    public function edit(Product $product, Request $request): Response
    {
        //Vérifier s'il existe au moins une catégorie avant de continuer
        if ( \count($this->categoryRepository->findAll()) == 0 )
        {
            $this->addFlash("warning", "Pour modifier un produit, vous devez d'abord créer au moins une catégorie");
            return $this->redirectToRoute('admin_category_index');
        }

        $form = $this->createForm(AdminProductFormType::class, $product);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $product->setCreatedAt(new DateTimeImmutable());
            $product->setUpdatedAt(new DateTimeImmutable());

            $this->em->persist($product);

            $this->em->flush();

            $this->addFlash('success', "Le produit {$product->getName()} a été modifié avec succès.");

            return $this->redirectToRoute('admin_product_index');
        }

        return $this->render('pages/admin/products/edit.html.twig', [
            "form" => $form->createView(),
            "product" => $product
        ]);
    }

    #[Route('/product//{id<\d+>}/delete', name: 'admin_product_delete', methods: ['DELETE'])]
    public function delete(Product $product, Request $request): Response
    {
        if ( $this->isCsrfTokenValid('delete_product_'.$product->getId(), $request->request->get('_csrf_token')) )
        {
            $this->em->remove($product);

            $this->em->flush();

            $this->addFlash('success', "Le produit {$product->getName()} a été supprimée");
            
            return $this->redirectToRoute('admin_product_index');
        }

    }
}
