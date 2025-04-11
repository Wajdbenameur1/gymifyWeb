<?php
namespace App\Form;

use App\Entity\Post;
use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'attr' => ['class' => 'form-control'], // Classe Bootstrap pour les champs de texte
            ])
            ->add('content', TextType::class, [
                'attr' => ['class' => 'form-control'], // Classe Bootstrap pour le contenu
            ])
            ->add('image_url', TextType::class, [
                'required' => false,
                'attr' => ['class' => 'form-control'], // Classe Bootstrap pour l'URL de l'image
            ])
            ->add('createdAt', DateTimeType::class, [
                'widget' => 'single_text',
                'required' => false,
                'attr' => ['class' => 'form-control'], // Classe Bootstrap pour le champ de date/heure
            ])
            ->add('user', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'username', // ou 'email', 'nom', etc.
                'placeholder' => 'Choisir un utilisateur',
                'attr' => ['class' => 'form-control'], // Classe Bootstrap pour le champ d'utilisateur
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Post::class,
        ]);
    }
}
