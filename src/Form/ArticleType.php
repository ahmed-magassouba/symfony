<?php

namespace App\Form;

use App\Entity\Article;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('title', TextType::class, [
            'label' => 'Titre',
            'attr' => [
                'placeholder' => 'Titre de l\'article',
                'class' => 'form-control'
            ]
        ])
        ->add('image', FileType::class, [
            'label' => false,
            'multiple' => true, //pour plusieurs images
            'mapped' => false, //pour ne pas le lier à la base de données
            'required' => false,
            'attr' => [
                'placeholder' => 'Titre de l\'article',
                'class' => 'form-control'
            ]
        ])
        ->add('content', CKEditorType::class, [
            'label' => 'Contenu',
            'attr' => [
                'placeholder' => 'Contenu de l\'article',
                'class' => 'form-control'
            ]
        ])
        ->add('slug', TextType::class, [
            'label' => 'Slug',
            'attr' => [
                'placeholder' => 'Slug de l\'article',
                'class' => 'form-control'
            ]
        ])
        ->add('categorie', EntityType::class, [
            'class' => 'App\Entity\Categorie',
            'choice_label' => 'name',
            'label' => 'Catégorie',
            'attr' => [
                'class' => 'form-control'
            ]
        ]);
}

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
        ]);
    }
}
