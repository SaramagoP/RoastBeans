<?php

namespace App\Controller\Admin\Settings;


use DateTimeImmutable;
use App\Entity\Settings;
use App\Form\SettingsFormType;
use App\Repository\SettingsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin')]
class SettingsController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em, 
        private SettingsRepository $settingsRepository)
    {
    }

    #[Route('/settings', name: 'admin_settings_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('pages/admin/settings/index.html.twig', [
            "settings" => $this->settingsRepository->find(2) // dans mise en production pas oublier de metre 1
        ]);
    }

    #[Route('/settings/{id<\d+>}/edit', name: 'admin_settings_edit', methods: ['GET', 'PUT'])]
    public function edit(Settings $settings, Request $request): Response
    {
        $form = $this->createForm(SettingsFormType::class, $settings, [
            "method" => "PUT"
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $settings->setUpdatedAt(new DateTimeImmutable());

            $this->em->persist($settings);

            $this->em->flush();

            $this->addFlash('success', "Les paramètres du site ont été modifié.");

            return $this->redirectToRoute('admin_settings_index');
        }


        return $this->render('pages/admin/settings/edit.html.twig', [
            "form" => $form->createView()
        ]);
    }
}
