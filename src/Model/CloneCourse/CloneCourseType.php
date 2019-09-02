<?php

namespace App\Model\CloneCourse;

use App\Entity\Course\Course;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class CloneCourseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('courseToClone', EntityType::class, array(
                'class' => Course::class,
                'choice_label' => 'name',
                'label' => false,
                'choices' => $options['courses']
            ))
            ->add('name', TextType::class, array(
                'label' => false,
                'attr' => array('placeholder' => 'Name')
            ))
            ->add('startDate', DateType::class, array(
                'widget' => 'single_text',
                'format' => 'dd/MM/yyyy',
                'label' => false,
                'attr' => array('placeholder' => 'Start Date (dd/mm/yyyy)')
            ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => CloneCourse::class,
            'courses' => array()
        ));
    }
}
