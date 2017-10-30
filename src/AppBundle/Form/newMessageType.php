<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class newMessageType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('message', TextType::class, [
                'label' => false,
                'required' => true,
                'post_max_size_message' => 256,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Escribe un nuevo mensaje: ',
                    'maxlength' => 256
                ]
            ])
            ->add('Enviar', SubmitType::class, [
                'attr' => [
                    'class' => 'btn btn-dark'
                ]
            ]);
    }
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Message'
        ));
    }
}