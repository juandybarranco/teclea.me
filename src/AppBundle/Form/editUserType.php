<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class editUserType extends AbstractType
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
            ->add('location', TextType::class, [
                'label' => false,
                'required' => false,
                'attr' => [
                    'placeholder' => 'Localización: ',
                    'class' => 'form-control'
                ]
            ])
            ->add('personalMessage', TextType::class, [
                'label' => false,
                'post_max_size_message' => 150,
                'required' => false,
                'attr' => [
                    'placeholder' => 'Mensaje Personal: ',
                    'class' => 'form-conrol',
                    'maxlength' => 150
                ]
            ])
            ->add('Editar Perfil', SubmitType::class, [
                'attr' => [
                    'class' => 'btn btn-info btn-lg'
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