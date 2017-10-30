<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class newReplyPMType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('message', TextareaType::class, [
                'label' => false,
                'required' => true,
                'post_max_size_message' => 1024,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Escribe una respuesta: ',
                    'maxlength' => 1024
                ]
            ])
            ->add('Enviar', SubmitType::class, [
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
            'data_class' => 'AppBundle\Entity\PM'
        ));
    }
}