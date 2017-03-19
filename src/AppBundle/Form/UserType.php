<?php
namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', TextType::class, [
                'label' => false,
                'required' => true,
                'attr' => [
                    'placeholder' => 'Nombre de Usuario: ',
                    'class' => 'form-control'
                ]
            ])
            ->add('password', RepeatedType::class, [
                'label' => true,
                'required' => true,
                'type' => PasswordType::class,
                'mapped' => false,
                'invalid_message' => 'Las contraseñas no coinciden.',
                'first_options' => [
                    'label' => false,
                    'attr' => [
                        'placeholder' => 'Contraseña: ',
                        'class' => 'form-control'
                    ]
                ],
                'second_options' => [
                    'label' => false,
                    'attr' => [
                        'placeholder' => 'Repita la Contraseña: ',
                        'class' => 'form-control'
                    ]
                ]
            ])
            ->add('email', EmailType::class, [
                'label' => false,
                'required' => true,
                'attr' => [
                    'placeholder' => 'Correo Electrónico: ',
                    'class' => 'form-control'
                ]
            ])
            ->add('country', TextType::class, [
                'label' => false,
                'required' => true,
                'attr' => [
                    'placeholder' => 'País: ',
                    'class' => 'form-control'
                ]
            ])
            ->add('referred', TextType::class, [
                'label' => false,
                'required' => false,
                'attr' => [
                    'placeholder' => '[OPCIONAL] Usuario de Referido',
                    'class' => 'form-control'
                ]
            ])
            ->add('Enviar', SubmitType::class, [
                'attr' => [
                    'class' => 'btn btn-lg mt5'
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