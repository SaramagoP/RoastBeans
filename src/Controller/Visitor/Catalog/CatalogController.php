<?php

namespace App\Controller\Visitor\Catalog;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/catalog')]
class CatalogController extends AbstractController
{
    public function __construct(private productRepository $productRepository)
    {
    }


    #[Route('/', name: 'visitor_catalog_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('pages/visitor/catalog/index.html.twig', [
            "products" => $this->productRepository->findAll()
        ]);
    }


    #[Route('/{id}/{slug}/show', name: 'visitor_catalog_product_show', methods: ['GET'])]
    public function show(Product $product): Response
    {
        return $this->render('pages/visitor/catalog/show.html.twig',[
            "product" => $product
        ]);
    }


}
