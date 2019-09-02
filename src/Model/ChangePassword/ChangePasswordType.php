<?php

namespace App\Model\ChangePassword;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class ChangePasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('oldPassword', PasswordType::class, array(
                'label' => false,
                'attr' => array('placeholder' => 'Re-enter your current password')
            ))
            ->add('newPassword', RepeatedType::class, array(
                'type' => PasswordType::class,
                'invalid_message' => 'The new password fields must match',
                // place the second field first
                // hacky, but means the error will be displayed second if passwords don't match
                'second_options' => array(
                    'label' => false,
                    'attr' => array('placeholder' => 'New password')
                ),
                'first_options'  => array(
                    'label' => false,
                    'attr' => array('placeholder' => 'Repeat new password')
                )
            ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => ChangePassword::class
        ));
    }
}
