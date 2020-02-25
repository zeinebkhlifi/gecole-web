<?php

namespace EventBundle\Form;

use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('libelle', TextType::class, array(
                'required'=> false,
            ))
            ->add('description', CKEditorType::class, array(
                'attr' => array(
                    'class' => 'ckeditor'
                ),
            ))
            ->add('type', ChoiceType::class, [
                'required'=> false,
                'choices'  => [
                    'Musical' => 'Musical',
                    'Sporttif' => 'Sportif',
                    'Socicale' => 'Sociale',
                ],
            ])
            ->add('lieu', TextType::class, array(
                'required'=> false,
            ))
            ->add('date', DateType::class, array(
                'required'=> false,
            ) )
            ->add('nbrPlaces', NumberType::class, array(
                'required'=> false,
            ));
    }/**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'EventBundle\Entity\Event'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'eventbundle_event';
    }


}
