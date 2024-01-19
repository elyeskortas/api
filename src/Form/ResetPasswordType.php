<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;

class ResetPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('password', PasswordType::class,[
                'constraints' => [
                    new Length([
                        'min' => 8,
                        'minMessage' => 'Your password must be at least {{ limit }} characters long',
                    ]),
                ],
                'attr' => [
                    'class' => 'o3-form-control o3-input-lg',
                ]
            ])
            ->add('resetPassword', PasswordType::class,[
                'constraints' => [
                    new Length([
                        'min' => 8,
                        'minMessage' => 'Your password must be at least {{ limit }} characters long',
                    ]),
                ],
                'attr' => [
                    'class' => 'o3-form-control o3-input-lg',
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Submit',
                'attr' => [
                    'class' => 'mt-2 o3-btn o3-btn-primary o3-btn-block',
                ],
            ]);
    }

}
