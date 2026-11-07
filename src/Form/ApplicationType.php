<?php

namespace App\Form;

use App\Entity\Application;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class ApplicationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $image = static fn (): array => [new Assert\Image(
            maxSize: '4M',
            mimeTypes: ['image/jpeg', 'image/png', 'image/webp'],
            mimeTypesMessage: 'Formats acceptés : JPEG, PNG ou WebP.',
            maxSizeMessage: 'Fichier trop volumineux. Maximum : {{ limit }} {{ suffix }}.',
        )];
        $url = static fn (string $label): array => ['label' => $label, 'required' => false, 'default_protocol' => 'https', 'attr' => ['placeholder' => 'https://…']];

        $builder
            ->add('name', TextType::class, ['label' => 'Nom de l’application', 'attr' => ['maxlength' => 120]])
            ->add('slug', TextType::class, ['label' => 'Adresse de la page', 'required' => false, 'help' => 'Laissez vide : elle sera créée à partir du nom (ex. « mon-app »).'])
            ->add('tagline', TextType::class, ['label' => 'Accroche', 'required' => false, 'help' => 'Une phrase courte, affichée sous le nom.', 'attr' => ['maxlength' => 160]])
            ->add('shortDescription', TextareaType::class, ['label' => 'Description courte', 'required' => false, 'help' => 'Pour les moteurs de recherche et les aperçus de partage (400 caractères maximum).', 'attr' => ['rows' => 3, 'maxlength' => 400]])
            ->add('description', TextareaType::class, ['label' => 'Description complète', 'required' => false, 'help' => 'Format Markdown : **gras**, *italique*, ## titre, - liste, [lien](https://…). Le HTML brut est ignoré.', 'attr' => ['rows' => 14]])
            ->add('featuresText', TextareaType::class, ['label' => 'Points forts', 'required' => false, 'help' => 'Un point fort par ligne.', 'attr' => ['rows' => 6]])
            ->add('category', TextType::class, ['label' => 'Catégorie', 'required' => false, 'attr' => ['maxlength' => 60, 'placeholder' => 'Ex. : Productivité']])
            ->add('version', TextType::class, ['label' => 'Version', 'required' => false, 'attr' => ['maxlength' => 30, 'placeholder' => 'Ex. : 1.2.0']])
            ->add('accentColor', ColorType::class, ['label' => 'Couleur de l’application', 'help' => 'Utilisée sur sa page (boutons, accents).'])
            ->add('iconFile', FileType::class, ['label' => 'Icône', 'mapped' => false, 'required' => false, 'help' => 'Carrée de préférence (512×512). JPEG, PNG ou WebP, 4 Mo maximum.', 'constraints' => $image(), 'attr' => ['accept' => 'image/jpeg,image/png,image/webp']])
            ->add('removeIcon', CheckboxType::class, ['label' => 'Supprimer l’icône actuelle', 'mapped' => false, 'required' => false])
            ->add('coverFile', FileType::class, ['label' => 'Image de couverture', 'mapped' => false, 'required' => false, 'help' => 'Large (1600×900 conseillé). Aussi utilisée pour les aperçus de partage.', 'constraints' => $image(), 'attr' => ['accept' => 'image/jpeg,image/png,image/webp']])
            ->add('removeCover', CheckboxType::class, ['label' => 'Supprimer la couverture actuelle', 'mapped' => false, 'required' => false])
            ->add('googlePlayUrl', UrlType::class, $url('Lien Google Play'))
            ->add('appStoreUrl', UrlType::class, $url('Lien App Store'))
            ->add('websiteUrl', UrlType::class, $url('Site web de l’application'))
            ->add('privacyUrl', UrlType::class, $url('Politique de confidentialité de l’application'))
            ->add('published', CheckboxType::class, ['label' => 'Publiée (visible sur le site)', 'required' => false])
            ->add('position', IntegerType::class, ['label' => 'Ordre d’affichage', 'help' => 'Les plus petits numéros apparaissent en premier.']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Application::class]);
    }
}
