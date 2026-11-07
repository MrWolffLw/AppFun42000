<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

/** Ajout d'une vidéo : lien YouTube/Vimeo OU fichier MP4/WebM. */
class VideoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('url', UrlType::class, ['label' => 'Lien YouTube ou Vimeo', 'required' => false, 'default_protocol' => 'https', 'attr' => ['placeholder' => 'https://www.youtube.com/watch?v=…']])
            ->add('file', FileType::class, [
                'label' => 'ou un fichier vidéo (MP4 ou WebM)', 'required' => false,
                'help' => '256 Mo maximum. Pour de longues vidéos, préférez un lien YouTube ou Vimeo.',
                'attr' => ['accept' => 'video/mp4,video/webm'],
                'constraints' => [new Assert\File(
                    maxSize: '256M',
                    mimeTypes: ['video/mp4', 'video/webm'],
                    mimeTypesMessage: 'Formats acceptés : MP4 ou WebM.',
                    maxSizeMessage: 'Vidéo trop volumineuse. Maximum : {{ limit }} {{ suffix }}.',
                )],
            ])
            ->add('caption', TextType::class, ['label' => 'Légende (facultative)', 'required' => false, 'attr' => ['maxlength' => 200],
                'constraints' => [new Assert\Length(max: 200)]]);
    }
}
