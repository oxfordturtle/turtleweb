<?php

namespace App\Entity\User;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\DateType;

class EditStudentDetailsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', TextType::class, array(
                'label' => false,
                'attr' => array('placeholder' => 'Username (choose any)')
            ))
            ->add('email', EmailType::class, array(
                'label' => false,
                'attr' => array('placeholder' => 'Email Address')
            ))
            ->add('firstname', TextType::class, array(
                'label' => false,
                'attr' => array('placeholder' => 'Firstname')
            ))
            ->add('surname', TextType::class, array(
                'label' => false,
                'attr' => array('placeholder' => 'Surname')
            ))
            ->add('schoolName', TextType::class, array(
                'label' => false,
                'attr' => array('placeholder' => 'Name')
            ))
            ->add('schoolPostcode', TextType::class, array(
                'label' => false,
                'attr' => array('placeholder' => 'Postcode')
            ))
            ->add('dateOfBirth', DateType::class, array(
                'label' => false,
                'widget' => 'single_text',
                'format' => 'dd/MM/yyyy',
                'attr' => array('placeholder' => 'Date of Birth (dd/mm/yyyy)')
            ))
            ->add('homePostcode', TextType::class, array(
                'label' => false,
                'attr' => array('placeholder' => 'Home Postcode')
            ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => User::class,
            'validation_groups' => array('student'),
            'urn_path' => ''
        ));
    }
}
