<?php

namespace App\Controller\Admin;

use App\Form\SettingType;
use App\Repository\SiteSettingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class SettingsController extends AbstractController
{
    #[Route('/admin/settings', name: 'admin_settings', methods: ['GET', 'POST'])]
    public function index(Request $request, SiteSettingRepository $repo, EntityManagerInterface $em): Response
    {
        $setting = $repo->get();
        $form = $this->createForm(SettingType::class, $setting);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($setting);
            $em->flush();
            $this->addFlash('success', 'Réglages enregistrés.');

            return $this->redirectToRoute('admin_settings');
        }

        return $this->render('admin/settings.html.twig', ['form' => $form]);
    }
}
