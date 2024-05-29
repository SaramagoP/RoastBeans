<?php

namespace App\Controller\Admin\Review;


use App\Entity\Review;
use App\Repository\ReviewRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/review')]
class ReviewController extends AbstractController
{
    #[Route('/', name: 'admin_review_index', methods: ['GET'])]
    public function index(ReviewRepository $reviewRepository): Response
    {
        $reviews = $reviewRepository->findAll();

        return $this->render('pages/admin/review/index.html.twig', [
            "reviews" =>$reviews
        ]);
    }

    
    #[Route('/admin/review/{id<\d+>}/delete', name: 'admin_review_delete', methods: ['DELETE'])]
    public function delete(Review $review, Request $request, EntityManagerInterface $em): Response
    {
        if ( $this->isCsrfTokenValid('delete_review_'.$review->getId(), $request->request->get('_csrf_token')) )
        {
            $this->addFlash('success', "Le commentaire a été supprimée avec succès.");
            
            $em->remove($review);
            
            $em->flush();
            
            return $this->redirectToRoute('admin_review_index');
        }
    }
}
