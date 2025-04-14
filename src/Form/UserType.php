<?php
namespace App\Form;

use App\Entity\User;
use App\Enum\Role;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints\File;

class UserType extends AbstractType
{public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom')
            ->add('prenom')
            ->add('email', EmailType::class)
            ->add('password', PasswordType::class)
            ->add('dateNaissance', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('role', ChoiceType::class, [
                'choices' => Role::cases(), // Donne toutes les valeurs possibles de l'enum
                'choice_label' => fn(Role $role) => match($role) {
                    Role::ADMIN => 'Admin',
                    Role::SPORTIF => 'Sportif',
                    Role::RESPONSABLE_SALLE => 'Responsable Salle',
                    Role::ENTRAINEUR => 'Entraîneur',
                },
                'choice_value' => fn(?Role $role) => $role?->value,
                'required' => true,
            ])
            ->add('specialite', TextType::class, [
                'required' => false,
                'attr' => ['id' => 'specialite_field'], // pour JS
            ])
            ->add('imageUrl', FileType::class, [
                'label' => 'Profile Image',
                'mapped' => false,  // This is necessary if you are not directly mapping the image URL to an entity field
                'required' => false,
                'attr' => [
                    'accept' => 'image/jpeg,image/png,image/webp',
                ],
            ]);
          
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}