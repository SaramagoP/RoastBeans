<?php

namespace App\Controller\Visitor\About;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AboutController extends AbstractController
{
    #[Route('/about', name: 'visitor_about_index')]
    public function index(): Response
    {
        return $this->render('pages/visitor/about/index.html.twig');
    }
}
