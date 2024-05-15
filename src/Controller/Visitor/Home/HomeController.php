<?php

namespace App\Controller\Visitor\Home;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'visitor_home_index', methods:['GET'])]
    public function index(): Response
    {
        return $this->render('pages/visitor/home/index.html.twig');
    }
}
