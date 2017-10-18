<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChangePasswordType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('password', RepeatedType::class, [
                'label' => true,
                'required' => true,
                'type' => PasswordType::class,
                'mapped' => false,
                'invalid_message' => 'Las contraseñas no coinciden.',
                'first_options' => [
                    'label' => false,
                    'attr' => [
                        'placeholder' => 'Nueva contraseña: ',
                        'class' => 'form-control'
                    ]
                ],
                'second_options' => [
                    'label' => false,
                    'attr' => [
                        'placeholder' => 'Repita la nueva contraseña: ',
                        'class' => 'form-control'
                    ]
                ]
            ])
            ->add('Cambiar Contraseña', SubmitType::class, [
                'label' => 'Cambiar Contraseña',
                'attr' => [
                    'class' => 'btn btn-secondary btn-lg'
                ]
            ]);
    }
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\User'
        ));
    }
}