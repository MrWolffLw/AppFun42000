<?php

namespace App\Form;

use App\Entity\SiteSetting;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SettingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $md = 'Format Markdown. Le HTML brut est ignoré.';
        $builder
            ->add('siteName', TextType::class, ['label' => 'Nom du site'])
            ->add('heroTitle', TextType::class, ['label' => 'Grand titre de la page d’accueil'])
            ->add('heroSubtitle', TextareaType::class, ['label' => 'Sous-titre', 'attr' => ['rows' => 3]])
            ->add('metaDescription', TextareaType::class, ['label' => 'Description pour les moteurs de recherche', 'attr' => ['rows' => 3]])
            ->add('contactEmail', EmailType::class, ['label' => 'E-mail de contact affiché sur la page Contact', 'required' => false])
            ->add('aboutText', TextareaType::class, ['label' => 'Page « À propos »', 'help' => $md, 'attr' => ['rows' => 10]])
            ->add('legalText', TextareaType::class, ['label' => 'Page « Mentions légales »', 'help' => $md, 'attr' => ['rows' => 10]])
            ->add('privacyText', TextareaType::class, ['label' => 'Page « Confidentialité »', 'help' => $md, 'attr' => ['rows' => 10]]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => SiteSetting::class]);
    }
}
