<?php

namespace App\Entity\User;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class CreateStudentParentType extends AbstractType
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
            ->add('password', RepeatedType::class, array(
                'type' => PasswordType::class,
                'invalid_message' => 'The passwords do not match',
                // place the second field first
                // hacky, but means the error will be displayed second if passwords don't match
                'second_options' => array(
                    'label' => false,
                    'attr' => array('placeholder' => 'Password')
                ),
                'first_options'  => array(
                    'label' => false,
                    'attr' => array('placeholder' => 'Repeat Password')
                )
            ))
            ->add('parentFirstname', TextType::class, array(
                'label' => false,
                'attr' => array('placeholder' => 'Firstname')
            ))
            ->add('parentSurname', TextType::class, array(
                'label' => false,
                'attr' => array('placeholder' => 'Surname')
            ))
            ->add('firstname', TextType::class, array(
                'label' => false,
                'attr' => array('placeholder' => 'Firstname')
            ))
            ->add('surname', TextType::class, array(
                'label' => false,
                'attr' => array('placeholder' => 'Surname')
            ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => User::class,
            'validation_groups' => array('registration', 'student'),
            'urn_path' => false
        ));
    }
}
