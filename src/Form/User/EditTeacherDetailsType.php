<?php

namespace App\Form\User;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;

class EditTeacherDetailsType extends AbstractType
{
  public function buildForm(FormBuilderInterface $builder, array $options)
  {
    $builder
      ->add('username', null, ['label' => 'Username (choose any)'])
      ->add('email', null, ['label' => 'Email Address'])
      ->add('firstname')
      ->add('surname')
      ->add('schoolName', null, ['label' => 'School Name (optional)', 'required' => false])
      ->add('schoolPostcode', null, ['label' => 'School Postcode (optional)', 'required' => false]);
  }

  public function configureOptions(OptionsResolver $resolver)
  {
    $resolver->setDefaults([
      'data_class' => User::class,
      'validation_groups' => ['edit']
    ]);
  }
}
