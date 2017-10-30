<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommunityType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => false,
                'required' => true,
                'attr' => [
                    'placeholder' => 'Nombre de la Comunidad: ',
                    'class' => 'form-control'
                ]
            ])
            ->add('description', TextType::class, [
                'label' => false,
                'required' => true,
                'attr' => [
                    'placeholder' => 'Descripción de la Comunidad: ',
                    'class' => 'form-control'
                ]
            ])
            ->add('privacy', ChoiceType::class, [
                'label' => false,
                'required' => true,
                'attr' => [
                    'class' => 'form-control'
                ],
                'choices' => [
                    'Pública' => 'public',
                    'Protegida' => 'protected',
                    'Privada' => 'private'
                ]
            ])
            ->add('Crear', SubmitType::class, [
                'attr' => [
                    'class' => 'btn btn-lg btn-dark'
                ]
            ]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Community'
        ));
    }
}