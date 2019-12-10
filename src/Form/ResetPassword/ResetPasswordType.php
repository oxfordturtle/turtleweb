<?php

namespace App\Form\ResetPassword;

use App\Model\ResetPassword;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ResetPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('password', RepeatedType::class, array(
                'type' => PasswordType::class,
                'invalid_message' => 'The new password fields must match',
                // place the second field first
                // hacky, but means the error will be displayed second if passwords don't match
                'second_options' => array(
                    'label' => false,
                    'attr' => array('placeholder' => 'Repeat new password')
                ),
                'first_options'  => array(
                    'label' => false,
                    'attr' => array('placeholder' => 'New password')
                )
            ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => ResetPassword::class
        ));
    }
}
