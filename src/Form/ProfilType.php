<?php

namespace App\Form;

use App\Entity\Participant;
use App\Entity\Site;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class ProfilType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('prenom', TextType::class)
            ->add('nom', TextType::class)
            ->add('telephone', TelType::class)
            ->add('email', EmailType::class)
            ->add('password', PasswordType::class, [
                'mapped' => false,
                'required' => false,
                'label' => 'Nouveau mot de passe'
            ])
            ->add('confirmPassword', PasswordType::class, [
                'mapped' => false,
                'required' => false,
                'label' => 'Confirmation'
            ])
            ->add('site', EntityType::class, [
                'class' => Site::class,
                    'choice_label' => 'nom',
            ])
            ->add('photo', FileType::class, [
                'label' => 'Photo de profil (jpg, png)',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/webp',
                        ],
                        'mimeTypesMessage' => 'Veuillez télécharger une image valide (jpg, png, webp)',
                    ])
                ],
            ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Participant::class,
        ]);
    }
}
