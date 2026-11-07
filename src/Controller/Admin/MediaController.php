<?php

namespace App\Controller\Admin;

use App\Entity\Application;
use App\Entity\Media;
use App\Form\ImageUploadType;
use App\Form\VideoType;
use App\Repository\MediaRepository;
use App\Service\FileUploader;
use App\Service\VideoUrlParser;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class MediaController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly MediaRepository $mediaRepo,
        private readonly FileUploader $uploader,
        private readonly VideoUrlParser $parser,
    ) {}

    #[Route('/admin/apps/{id}/media/images', name: 'admin_media_images', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function images(Application $application, Request $request): Response
    {
        $form = $this->createForm(ImageUploadType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $position = $this->mediaRepo->nextPosition($application);
            $count = 0;
            foreach ((array) $form->get('files')->getData() as $file) {
                if (!$file instanceof UploadedFile) {
                    continue;
                }
                $media = (new Media())->setType(Media::IMAGE)->setFilename($this->uploader->upload($file, 'images'))->setPosition($position++);
                $application->addMedia($media);
                $this->em->persist($media);
                ++$count;
            }
            $this->em->flush();
            $this->addFlash('success', $count > 1 ? $count.' images ajoutées.' : 'Image ajoutée.');
        } else {
            $this->flashErrors($form, 'Aucun fichier reçu. Vérifiez la taille des images (limite du serveur) et réessayez.');
        }

        return $this->back($application);
    }

    #[Route('/admin/apps/{id}/media/video', name: 'admin_media_video', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function video(Application $application, Request $request): Response
    {
        $form = $this->createForm(VideoType::class);
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            $this->flashErrors($form, 'Aucune donnée reçue. Si vous envoyez un fichier, vérifiez la limite de taille du serveur.');

            return $this->back($application);
        }

        $url = trim((string) $form->get('url')->getData());
        $file = $form->get('file')->getData();
        $media = (new Media())->setCaption((string) $form->get('caption')->getData())->setPosition($this->mediaRepo->nextPosition($application));

        if ($file instanceof UploadedFile && $url !== '') {
            $this->addFlash('error', 'Choisissez soit un fichier, soit un lien, pas les deux.');

            return $this->back($application);
        }
        if ($file instanceof UploadedFile) {
            $media->setType(Media::VIDEO)->setFilename($this->uploader->upload($file, 'videos'));
        } elseif ($url !== '') {
            $parsed = $this->parser->parse($url);
            if ($parsed === null) {
                $this->addFlash('error', 'Lien non reconnu. Seuls les liens YouTube et Vimeo sont acceptés.');

                return $this->back($application);
            }
            $media->setType(Media::EMBED)->setEmbedProvider($parsed['provider'])->setEmbedId($parsed['id']);
        } else {
            $this->addFlash('error', 'Ajoutez un lien YouTube/Vimeo ou un fichier vidéo.');

            return $this->back($application);
        }

        $application->addMedia($media);
        $this->em->persist($media);
        $this->em->flush();
        $this->addFlash('success', 'Vidéo ajoutée.');

        return $this->back($application);
    }

    #[Route('/admin/media/{id}/caption', name: 'admin_media_caption', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function caption(Media $media, Request $request): Response
    {
        $this->checkCsrf($request, 'media-'.$media->getId());
        $media->setCaption(mb_substr((string) $request->request->get('caption'), 0, 200));
        $this->em->flush();
        $this->addFlash('success', 'Légende enregistrée.');

        return $this->back($media->getApplication());
    }

    #[Route('/admin/media/{id}/move/{dir}', name: 'admin_media_move', requirements: ['id' => '\d+', 'dir' => 'up|down'], methods: ['POST'])]
    public function move(Media $media, string $dir, Request $request): Response
    {
        $this->checkCsrf($request, 'media-'.$media->getId());
        $application = $media->getApplication();
        $list = $application->getMedia()->toArray();
        $i = array_search($media, $list, true);
        $j = $dir === 'up' ? $i - 1 : $i + 1;
        if ($i !== false && isset($list[$j])) {
            [$list[$i], $list[$j]] = [$list[$j], $list[$i]];
        }
        foreach ($list as $k => $m) {
            $m->setPosition($k + 1);
        }
        $this->em->flush();

        return $this->back($application);
    }

    #[Route('/admin/media/{id}/delete', name: 'admin_media_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(Media $media, Request $request): Response
    {
        $this->checkCsrf($request, 'media-'.$media->getId());
        $application = $media->getApplication();
        $this->uploader->removeMediaFile($media);
        $application->removeMedia($media);
        $this->em->remove($media);
        $this->em->flush();
        $this->addFlash('success', 'Média supprimé.');

        return $this->back($application);
    }

    private function back(Application $application): Response
    {
        return $this->redirectToRoute('admin_app_edit', ['id' => $application->getId(), '_fragment' => 'medias']);
    }

    private function flashErrors(FormInterface $form, string $fallback): void
    {
        $shown = false;
        foreach ($form->getErrors(true) as $error) {
            $this->addFlash('error', $error->getMessage());
            $shown = true;
        }
        if (!$shown) {
            $this->addFlash('error', $fallback);
        }
    }

    private function checkCsrf(Request $request, string $id): void
    {
        if (!$this->isCsrfTokenValid($id, (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Jeton de sécurité invalide. Rechargez la page.');
        }
    }
}
