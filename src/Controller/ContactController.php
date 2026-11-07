<?php

namespace App\Controller;

use App\Entity\ContactMessage;
use App\Form\ContactType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Routing\Attribute\Route;

class ContactController extends AbstractController
{
    public function __construct(private readonly string $contactTo, private readonly string $contactFrom) {}

    #[Route('/contact', name: 'contact', methods: ['GET', 'POST'])]
    public function index(Request $request, EntityManagerInterface $em, MailerInterface $mailer, RateLimiterFactory $contactLimiter): Response
    {
        $message = new ContactMessage();
        $form = $this->createForm(ContactType::class, $message);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $thanks = 'Merci, votre message a bien été envoyé. Je vous réponds rapidement.';

            // Robot : le champ piège est rempli. On fait semblant d'accepter, sans rien enregistrer.
            if (trim((string) $form->get('website')->getData()) !== '') {
                $this->addFlash('success', $thanks);

                return $this->redirectToRoute('contact');
            }
            if (!$contactLimiter->create($request->getClientIp() ?? 'inconnu')->consume(1)->isAccepted()) {
                $this->addFlash('error', 'Trop de messages envoyés depuis votre connexion. Réessayez plus tard.');

                return $this->redirectToRoute('contact');
            }

            $em->persist($message);
            $em->flush();

            try {
                $mailer->send((new Email())
                    ->from($this->contactFrom)->to($this->contactTo)->replyTo($message->getEmail())
                    ->subject('Nouveau message du site')
                    ->text($message->getMessage()."\n\n— ".$message->getName().' <'.$message->getEmail().'>'));
            } catch (\Throwable) {
                // Le message reste consultable dans l'administration même si l'e-mail n'a pas pu partir.
            }

            $this->addFlash('success', $thanks);

            return $this->redirectToRoute('contact');
        }

        return $this->render('contact/index.html.twig', ['form' => $form]);
    }
}
