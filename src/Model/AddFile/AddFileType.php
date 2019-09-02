<?php

namespace App\Model\AddFile;

use App\Entity\File\File;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class AddFileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('file', EntityType::class, array(
                'class' => File::class,
                'choice_label' => 'name',
                'label' => false,
                'choices' => $options['files']
            ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => AddFile::class,
            'files' => array()
        ));
    }
}
