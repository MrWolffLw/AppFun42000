<?php

namespace App\Controller;

use App\Repository\ApplicationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ShowcaseController extends AbstractController
{
    #[Route('/apps/{slug}', name: 'app_show', requirements: ['slug' => '[a-z0-9-]+'])]
    public function show(string $slug, ApplicationRepository $repo): Response
    {
        $application = $repo->findOneBy(['slug' => $slug]);
        // Une application non publiée n'est visible que par l'administrateur (aperçu).
        if (!$application || (!$application->isPublished() && !$this->isGranted('ROLE_ADMIN'))) {
            throw $this->createNotFoundException();
        }

        $others = array_values(array_filter(
            $repo->findPublished(),
            static fn ($a): bool => $a->getId() !== $application->getId(),
        ));

        return $this->render('showcase/show.html.twig', ['application' => $application, 'others' => $others]);
    }
}
