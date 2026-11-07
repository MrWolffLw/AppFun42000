<?php

namespace App\Controller;

use App\Repository\ApplicationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SitemapController extends AbstractController
{
    #[Route('/sitemap.xml', name: 'sitemap')]
    public function sitemap(ApplicationRepository $repo): Response
    {
        return new Response(
            $this->renderView('sitemap.xml.twig', ['apps' => $repo->findPublished()]),
            Response::HTTP_OK,
            ['Content-Type' => 'application/xml; charset=UTF-8'],
        );
    }

    #[Route('/robots.txt', name: 'robots')]
    public function robots(): Response
    {
        $body = "User-agent: *\nDisallow: /admin\nDisallow: /login\n\nSitemap: "
            .$this->generateUrl('sitemap', [], UrlGeneratorInterface::ABSOLUTE_URL)."\n";

        return new Response($body, Response::HTTP_OK, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
