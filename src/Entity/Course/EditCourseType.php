<?php

namespace App\Entity\Course;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class EditCourseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, array(
                'label' => false,
                'attr' => array('placeholder' => 'Course Name')
            ))
            ->add('startDate', DateType::class, array(
                'widget' => 'single_text',
                'format' => 'dd/MM/yyyy',
                'label' => false,
                'attr' => array('placeholder' => 'Start Date (dd/mm/yyyy)')
            ))
            ->add('status', ChoiceType::class, array(
                'choices' => array('Open' => 'open', 'Closed' => 'closed', 'Archived' => 'archived'),
                'label' => false
            ))
            ->add('description', TextareaType::class, array(
                'required' => false,
                'label' => false,
                'attr' => array('placeholder' => 'Enter a short description of the course (optional)')
            ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Course::class
        ));
    }
}
