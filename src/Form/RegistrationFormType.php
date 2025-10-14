<?php

namespace App\Form;

use App\Entity\Participant;
use App\Entity\Site;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom',
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Saisir votre nom',
                    ]),
                    new Length([
                        'min' => 3,
                        'minMessage' => 'Saisissez un minimum de {{limit}} caractères.',
                        'max' => 30,
                        'maxMessage' => 'Votre nom ne doit pas dépasser {{limit}} caractères.'
                    ])
                ],
            ])
            ->add('prenom', TextType::class, [
                'label' => 'Prenom',
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Saisir votre prenom',
                    ]),
                    new Length([
                    'min' => 3,
                    'minMessage' => 'Saisissez un minimum de {{limit}} caractères.',
                    'max' => 30,
                    'maxMessage' => 'Votre prenom ne doit pas dépasser {{limit}} caractères.'
                    ]),
                ],
            ])
            ->add('pseudo', TextType::class, [
                'label' => 'Pseudo',
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Saisir votre pseudo',
                    ]),
                    new Length([
                        'max' => 40,
                        'maxMessage' => 'Votre pseudo ne doit pas dépasser {{limite}} caractères.'
                    ])

                ]
            ])
            ->add('telephone', TextType::class, [
                'label' => 'Telephone',
                'constraints' => [
                    new Length([
                        'min' => 10,
                        'max' => 10,
                        'minMessage' => 'Si vous souhaitez préciser un telephone {{ limit }} chiffres.',
                        'maxMessage' => 'Si vous souhaitez préciser un telephone {{ limit }} chiffres, directement par 06 pas de +33'
                    ])
                ]
            ])
            ->add('email', EmailType::class, [
                'attr' => ['placeholder' => 'Email'],
                'label' => 'Email',
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Saisir votre email',
                    ])
                ]
            ])

            ->add('password', PasswordType::class, [
                                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Saisir votre password',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
            ])
            ->add('site', EntityType::class, [
                'class' => Site::class,
                    'placeholder' => 'Choisir un site',
                    'choice_label' => 'nom',
                ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Participant::class,
        ]);
    }
}
