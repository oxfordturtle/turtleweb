<?php

namespace App\Form\User;

use App\Model\ChangePassword;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

/**
 * Form type for changing a password.
 */
class ChangePasswordType extends AbstractType
{
  /**
   * Build the form.
   *
   * @param FormBuilderInterface $builder
   * @param array $options
   */
  public function buildForm(FormBuilderInterface $builder, array $options)
  {
    $builder
      ->add('oldPassword', PasswordType::class, ['label' => 'Re-enter your current password'])
      ->add('newPassword', RepeatedType::class, [
        'type' => PasswordType::class,
        'invalid_message' => 'The new password fields must match.',
        'first_options'  => ['label' => 'Repeat new password'],
        'second_options' => ['label' => 'New password']
      ]);
  }

  /**
   * Configure the form options.
   *
   * @param OptionsResolver $resolver
   */
  public function configureOptions(OptionsResolver $resolver)
  {
    $resolver->setDefaults(['data_class' => ChangePassword::class]);
  }
}
