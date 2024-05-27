<?php

namespace App\Controller\Visitor\Review;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ReviewController extends AbstractController
{
    #[Route('/review', name: 'visitor_review_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('pages/visitor/review/index.html.twig');
    }
}
