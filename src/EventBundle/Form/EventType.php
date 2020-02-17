<?php

namespace EventBundle\Form;

use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
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
            ->add('libelle')
            ->add('description', CKEditorType::class, array(
                'attr' => array(
                    'class' => 'eventDescription'
                ),
            ))
            ->add('type', ChoiceType::class, [
                'choices'  => [
                    'Musical' => 'Musical',
                    'Sport' => 'Sportif',
                    'Socicale' => 'Sociale',
                ],
            ])
            ->add('lieu')
            ->add('date', DateType::class)
            ->add('nbrPlaces');
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
