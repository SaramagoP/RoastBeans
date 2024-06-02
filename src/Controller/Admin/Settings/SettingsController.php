<?php

namespace App\Controller\Admin\Settings;

use DateTimeImmutable;
use App\Entity\Settings;
use App\Form\SettingsFormType;
use App\Repository\SettingsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


#[Route('/admin')] // Préfixe de route pour toutes les actions de ce contrôleur
class SettingsController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em, // Injection de dépendance de EntityManagerInterface
        private SettingsRepository $settingsRepository // Injection de dépendance de SettingsRepository
    ) {}

    #[Route('/settings', name: 'admin_settings_index', methods: ['GET'])] // Route pour afficher l'index des paramètres
    public function index(): Response
    {
        $settings = $this->settingsRepository->findAll(); // Récupère tous les donées en base de données
        
        if (empty($settings)) // Vérifie si les paramètres sont vides
        {
            return $this->render('pages/admin/settings/index.html.twig', [ 
                
                "settings" => [] // Rendre la vue avec un tableau vide si aucun paramètre n'est trouvé
            ]);
        }

        
    
        $settings = $settings[0]; // Supposons qu'il y a un seul enregistrement de paramètres, récupérer le premier élément
    
        return $this->render('pages/admin/settings/index.html.twig', [
            // $this : Fait référence à l'instance actuelle du contrôleur
            // render : Est une méthode du contrôleur qui sert à affucher  la vue du template.
            // 'pages/admin/settings/index.html.twig' : Est le chemin vers le fichier de template Twig que l'on souhaite rendre.
            "settings" => $settings // Rendre la vue avec les paramètres récupérés
            // clé 
        ]);
    }

    #[Route('/settings/{id<\d+>}/edit', name: 'admin_settings_edit', methods: ['GET', 'POST'])] // Route pour éditer un paramètre spécifique
    public function edit(Settings $settings, Request $request): Response
    {
        $form = $this->createForm(SettingsFormType::class, $settings, [ // Crée un formulaire pour éditer les paramètres
            "method" => "POST" // Méthode HTTP pour le formulaire
        ]);

        $form->handleRequest($request); // Traite la requête HTTP avec les données du formulaire

        if ($form->isSubmitted() && $form->isValid()) // Vérifie si le formulaire est soumis et valide
        {
            $settings->setUser($this->getUser());

            $settings->setUpdatedAt(new DateTimeImmutable()); // Met à jour le champ 'updatedAt' avec la date et l'heure actuelles

            $this->em->persist($settings); // Persiste les modifications apportées aux paramètres
            
            $this->em->flush(); // Enregistre les modifications dans la base de données

            $this->addFlash('success', 'Les paramètres du site ont été modifiés.'); // Ajoute un message flash de succès

            return $this->redirectToRoute('admin_settings_index'); // Redirige vers la route de l'index des paramètres
        }

        return $this->render('pages/admin/settings/edit.html.twig', [ // Quand tu click sur le button tu va sur la page edit pour poivoir faire des modifications sur les parametres. Donc rend la vue du formulaire d'édition
            "form" => $form->createView() // Passe la vue du formulaire à la vue Twig
        ]);
    }
}