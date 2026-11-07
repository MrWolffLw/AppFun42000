<?php

namespace App\Controller\Admin;

use App\Entity\ContactMessage;
use App\Repository\ContactMessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin/messages')]
class MessageController extends AbstractController
{
    #[Route('', name: 'admin_messages', methods: ['GET'])]
    public function index(ContactMessageRepository $repo): Response
    {
        return $this->render('admin/messages/index.html.twig', ['messages' => $repo->findBy([], ['createdAt' => 'DESC'])]);
    }

    #[Route('/{id}', name: 'admin_message_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(ContactMessage $message, EntityManagerInterface $em): Response
    {
        if (!$message->isRead()) {
            $message->setRead(true);
            $em->flush();
        }

        return $this->render('admin/messages/show.html.twig', ['message' => $message]);
    }

    #[Route('/{id}/delete', name: 'admin_message_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(ContactMessage $message, Request $request, EntityManagerInterface $em): Response
    {
        if (!$this->isCsrfTokenValid('message-'.$message->getId(), (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Jeton de sécurité invalide.');
        }
        $em->remove($message);
        $em->flush();
        $this->addFlash('success', 'Message supprimé.');

        return $this->redirectToRoute('admin_messages');
    }
}
