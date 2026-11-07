<?php

namespace App\Controller\Admin;

use App\Entity\Media;
use App\Repository\ApplicationRepository;
use App\Repository\ContactMessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class DashboardController extends AbstractController
{
    #[Route('/admin', name: 'admin_dashboard')]
    public function index(ApplicationRepository $apps, ContactMessageRepository $messages, EntityManagerInterface $em): Response
    {
        return $this->render('admin/dashboard.html.twig', [
            'total' => $apps->count([]),
            'published' => $apps->count(['published' => true]),
            'media' => $em->getRepository(Media::class)->count([]),
            'unread' => $messages->countUnread(),
        ]);
    }
}
