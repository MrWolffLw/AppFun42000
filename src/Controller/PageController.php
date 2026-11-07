<?php

namespace App\Controller;

use App\Repository\SiteSettingRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/** Pages de texte éditables depuis l'administration (Réglages). */
class PageController extends AbstractController
{
    #[Route('/a-propos', name: 'page_about')]
    public function about(SiteSettingRepository $settings): Response
    {
        return $this->render('page/show.html.twig', ['title' => 'À propos', 'content' => $settings->get()->getAboutText()]);
    }

    #[Route('/mentions-legales', name: 'page_legal')]
    public function legal(SiteSettingRepository $settings): Response
    {
        return $this->render('page/show.html.twig', ['title' => 'Mentions légales', 'content' => $settings->get()->getLegalText()]);
    }

    #[Route('/confidentialite', name: 'page_privacy')]
    public function privacy(SiteSettingRepository $settings): Response
    {
        return $this->render('page/show.html.twig', ['title' => 'Confidentialité', 'content' => $settings->get()->getPrivacyText()]);
    }
}
