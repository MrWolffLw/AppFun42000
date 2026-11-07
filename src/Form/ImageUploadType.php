<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

/** Envoi de plusieurs captures d'écran à la fois. */
class ImageUploadType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('files', FileType::class, [
            'label' => 'Choisir une ou plusieurs images',
            'multiple' => true,
            'help' => 'JPEG, PNG ou WebP, 8 Mo maximum chacune.',
            'attr' => ['accept' => 'image/jpeg,image/png,image/webp'],
            'constraints' => [
                new Assert\NotBlank(message: 'Choisissez au moins une image.'),
                new Assert\All([new Assert\Image(
                    maxSize: '8M',
                    mimeTypes: ['image/jpeg', 'image/png', 'image/webp'],
                    mimeTypesMessage: 'Formats acceptés : JPEG, PNG ou WebP.',
                    maxSizeMessage: 'Une image dépasse la taille maximale ({{ limit }} {{ suffix }}).',
                )]),
            ],
        ]);
    }
}
