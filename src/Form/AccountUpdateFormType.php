<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AccountUpdateFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => false,
                'attr' => [
                    'readonly' => true
                ]
            ])
            ->add('firstname', TextType::class, [
                'label' => false
            ])
            ->add('lastname', TextType::class, [
                'label' => false
            ])
            ->add('uuid', TextType::class, [
                'label' => false,
                'attr' => [
                    'readonly' => true
                ]
            ])
            ->add('profile_image', FileType::class, [
                'label' => false,
                'mapped' => false,
                'required' => false,
                'data_class' => null,
                'attr' => [
                    'style' => 'display: none' 
                ],
                'empty_data' => '-1'
                // 'constraints' => [
                //     new File([
                //         'maxSize' => '1024k',
                //         'mimeTypes' => [
                //             'image/*',
                //         ],
                //         'mimeTypesMessage' => 'Proszę wybrać odpowiedni format pliku!',
                //     ]),
                // ],
                
            ])
            ->add('plainPassword', PasswordType::class, [
                'label' => false,
                'required' => false,
                'empty_data' => '-1',
                'mapped' => false,
                'attr' => [
                    'placeholder' => 'Hasło',
                    'autocomplete' => 'new-password',
                    'placeholder' => 'Hasło',
                    'class' => 'password'
                ]
            ])
            ->add('last_chat', TextType::class, [
                'label' => false,
                'empty_data' => ''
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
