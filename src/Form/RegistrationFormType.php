<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\File;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // ->add('uuid')
            ->add('email', EmailType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Email'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Podaj email',
                    ]),
                ]
            ])
            ->add('firstname', TextType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Imię'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Podaj imię',
                    ]),
                ]
            ])
            ->add('lastname', TextType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Nazwisko'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Podaj nazwisko',
                    ]),
                ]
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'label' => 'Akceptuję regulamin',
                'constraints' => [
                    new IsTrue([
                        'message' => 'Musisz zaakceptować regulamin.',
                    ]),
                ],
            ])
            ->add('plainPassword', PasswordType::class, [
                'label' => false,
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
                'attr' => [
                    'autocomplete' => 'new-password',
                    'placeholder' => 'Hasło',
                    'class' => 'password'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Proszę podać hasło',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Twoje hasło powinno zawierać 6 znaków',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
            ])
            ->add('img', FileType::class, [
                'label' => false,
                'mapped' => false,
                // 'attr' => [
                //     'class' => 'form__input'
                // ],
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'image/*',
                        ],
                        'mimeTypesMessage' => 'Proszę wybrać odpowiedni format pliku!',
                    ]),
                    new NotBlank([
                        'message' => 'Dodaj zdjęcie',
                    ]),
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
