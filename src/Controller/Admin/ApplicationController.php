<?php

namespace App\Controller\Admin;

use App\Entity\Application;
use App\Form\ApplicationType;
use App\Form\ImageUploadType;
use App\Form\VideoType;
use App\Repository\ApplicationRepository;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\AsciiSlugger;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin/apps')]
class ApplicationController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ApplicationRepository $repo,
        private readonly FileUploader $uploader,
    ) {}

    #[Route('', name: 'admin_apps', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('admin/app/index.html.twig', ['apps' => $this->repo->findAllOrdered()]);
    }

    #[Route('/new', name: 'admin_app_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $application = (new Application())->setPosition($this->repo->nextPosition());
        $form = $this->createForm(ApplicationType::class, $application);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->finalize($application, $form);
            $this->em->persist($application);
            $this->em->flush();
            $this->addFlash('success', 'Application créée. Ajoutez maintenant des captures d’écran et des vidéos.');

            return $this->redirectToRoute('admin_app_edit', ['id' => $application->getId()]);
        }

        return $this->render('admin/app/form.html.twig', ['form' => $form, 'application' => $application, 'imageForm' => null, 'videoForm' => null]);
    }

    #[Route('/{id}/edit', name: 'admin_app_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(Application $application, Request $request): Response
    {
        $form = $this->createForm(ApplicationType::class, $application);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->finalize($application, $form);
            $this->em->flush();
            $this->addFlash('success', 'Modifications enregistrées.');

            return $this->redirectToRoute('admin_app_edit', ['id' => $application->getId()]);
        }

        $imageForm = $this->createForm(ImageUploadType::class, null, ['action' => $this->generateUrl('admin_media_images', ['id' => $application->getId()])]);
        $videoForm = $this->createForm(VideoType::class, null, ['action' => $this->generateUrl('admin_media_video', ['id' => $application->getId()])]);

        return $this->render('admin/app/form.html.twig', [
            'form' => $form,
            'application' => $application,
            'imageForm' => $imageForm->createView(),
            'videoForm' => $videoForm->createView(),
        ]);
    }

    #[Route('/{id}/toggle', name: 'admin_app_toggle', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function toggle(Application $application, Request $request): Response
    {
        $this->checkCsrf($request, 'app-'.$application->getId());
        $application->setPublished(!$application->isPublished());
        $this->em->flush();
        $this->addFlash('success', $application->isPublished() ? 'Application publiée.' : 'Application masquée du site.');

        return $this->redirectToRoute('admin_apps');
    }

    #[Route('/{id}/move/{dir}', name: 'admin_app_move', requirements: ['id' => '\d+', 'dir' => 'up|down'], methods: ['POST'])]
    public function move(Application $application, string $dir, Request $request): Response
    {
        $this->checkCsrf($request, 'app-'.$application->getId());
        $list = $this->repo->findAllOrdered();
        $i = array_search($application, $list, true);
        $j = $dir === 'up' ? $i - 1 : $i + 1;
        if ($i !== false && isset($list[$j])) {
            [$list[$i], $list[$j]] = [$list[$j], $list[$i]];
        }
        foreach ($list as $k => $a) {
            $a->setPosition($k + 1);
        }
        $this->em->flush();

        return $this->redirectToRoute('admin_apps');
    }

    #[Route('/{id}/delete', name: 'admin_app_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(Application $application, Request $request): Response
    {
        $this->checkCsrf($request, 'app-'.$application->getId());
        foreach ($application->getMedia() as $media) {
            $this->uploader->removeMediaFile($media);
        }
        $this->uploader->remove('icons', $application->getIconFilename());
        $this->uploader->remove('covers', $application->getCoverFilename());
        $this->em->remove($application);
        $this->em->flush();
        $this->addFlash('success', 'Application supprimée avec ses médias.');

        return $this->redirectToRoute('admin_apps');
    }

    private function finalize(Application $application, FormInterface $form): void
    {
        if ($application->getSlug() === '') {
            $base = (new AsciiSlugger('fr'))->slug($application->getName())->lower()->toString() ?: 'application';
            $slug = $base;
            $i = 2;
            while (($existing = $this->repo->findOneBy(['slug' => $slug])) && $existing->getId() !== $application->getId()) {
                $slug = $base.'-'.$i++;
            }
            $application->setSlug($slug);
        }
        $application->setIconFilename($this->swapImage($form, 'iconFile', 'removeIcon', 'icons', $application->getIconFilename()));
        $application->setCoverFilename($this->swapImage($form, 'coverFile', 'removeCover', 'covers', $application->getCoverFilename()));
    }

    private function swapImage(FormInterface $form, string $fileField, string $removeField, string $dir, ?string $current): ?string
    {
        $new = $current;
        if ($form->get($removeField)->getData()) {
            $this->uploader->remove($dir, $current);
            $new = null;
        }
        $file = $form->get($fileField)->getData();
        if ($file instanceof UploadedFile) {
            $this->uploader->remove($dir, $current);
            $new = $this->uploader->upload($file, $dir);
        }

        return $new;
    }

    private function checkCsrf(Request $request, string $id): void
    {
        if (!$this->isCsrfTokenValid($id, (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Jeton de sécurité invalide. Rechargez la page.');
        }
    }
}
