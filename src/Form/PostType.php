<?php

namespace App\Form;

use App\Entity\Post;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\OptionsResolver\OptionsResolver;


class PostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre du post',
                'attr' => [
                    'required'                           => 'required',
                    'data-parsley-minlength'             => '3',  // vous pouvez mettre 5 ici si nécessaire
                    'data-parsley-minlength-message'     => 'Le titre doit contenir au moins 3 caractères.',
                    'data-parsley-maxlength'             => '100',
                    'data-parsley-maxlength-message'     => 'Le titre ne peut pas dépasser 100 caractères.',
                    'data-parsley-pattern'               => '^(?!.*\b(spam|arnaque|insulte)\b).*$',
                    'data-parsley-trigger'               => 'keyup'
                ],
            ])
            ->add('content', TextType::class, [
                'label' => 'Contenu du post',
                'attr' => [
                    'required'                           => 'required',
                    'data-parsley-minlength'             => '10',  // vous pouvez mettre 5 ici si nécessaire
                    'data-parsley-minlength-message'     => 'Le contenu doit contenir au moins 10 caractères.',
                    'data-parsley-maxlength'             => '1000',
                    'data-parsley-maxlength-message'     => 'Le contenu ne peut pas dépasser 1000 caractères.',
                    'data-parsley-pattern'               => '^(?!.*\b(spam|arnaque|insulte)\b).*$',
                    'data-parsley-trigger'               => 'keyup'
                ],
            ])
            ->add('imageFile', FileType::class, [
                'label' => 'Image (fichier)',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/jpg',
                        ],
                        'mimeTypesMessage' => 'Veuillez uploader un fichier image valide (jpg ou png)',
                    ])
                ],
                'attr' => ['class' => 'form-control']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Post::class,
        ]);
    }
}
