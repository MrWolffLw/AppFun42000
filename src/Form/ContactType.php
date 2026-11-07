<?php

namespace App\Form;

use App\Entity\ContactMessage;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, ['label' => 'Votre nom', 'attr' => ['autocomplete' => 'name', 'maxlength' => 120]])
            ->add('email', EmailType::class, ['label' => 'Votre e-mail', 'attr' => ['autocomplete' => 'email', 'maxlength' => 180]])
            ->add('message', TextareaType::class, ['label' => 'Votre message', 'attr' => ['rows' => 7, 'maxlength' => 5000]])
            // Piège à robots : champ invisible pour les humains, rempli par les scripts automatiques.
            ->add('website', TextType::class, ['mapped' => false, 'required' => false, 'label' => false,
                'attr' => ['class' => 'hp', 'tabindex' => '-1', 'autocomplete' => 'off', 'aria-hidden' => 'true']]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => ContactMessage::class]);
    }
}
