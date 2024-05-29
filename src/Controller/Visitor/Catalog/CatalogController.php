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
    public function index(CategoryRepository $categoryRepository): Response
    {
        $categories = $categoryRepository->findAll();

        return $this->render('pages/visitor/catalog/index.html.twig', [
            "products" => $this->productRepository->findAll(),
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
        $reviews = new Review();

        $form = $this->createForm(ReviewFormType::class, $reviews);

        $form->handleRequest($request);

        if ( $form->isSubmitted() && $form->isValid() )
        {
            $reviews->setUser($this->getUser());
            $reviews->setProduct($product);

            $reviews->setCreatedAt(new DateTimeImmutable());

            $em->persist($reviews);

            $em->flush(); 

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
        Category $category,
        CategoryRepository $categoryRepository,
        ProductRepository $productRepository
    ): Response
    {
        $categories = $categoryRepository->findAll();
        $products = $productRepository->filterProductsByCategory($category->getId());

        return $this->render('pages/visitor/catalog/index.html.twig', [
            "products" => $products,
            "categories" => $categories,
        ]);
    }


}
