<?php

namespace App\Form;

use App\Entity\User;
use PharIo\Manifest\Email;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EditProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email' ,EmailType::class,[
                'label' => 'Email',
                'attr'=>['class' => 'form-control','placeholder'=>'Email']])
            ->add('lastname', TextType::class,['attr'=>['class' => 'form-control','placeholder'=>'Nom'],'label' => 'Nom'])
            ->add('firstname',TextType::class,['attr'=>['class' => 'form-control','placeholder'=>'Prénom'],'label' => 'Prénom'])
            ->add('pseudo' ,TextType::class,['attr'=>['class' => 'form-control','placeholder'=>'Pseudo'],'label' => 'Pseudo'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
