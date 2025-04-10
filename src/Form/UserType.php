<?php
// src/Form/UserType.php
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

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class)
            ->add('prenom', TextType::class)
            ->add('email', EmailType::class)
            ->add('password', PasswordType::class)
           
            ->add('dateNaissance', DateType::class, [
                'widget' => 'single_text',
            ])
            ->add('imageUrl', FileType::class, [
                'label' => 'Photo de profil',
                'required' => false,
                'mapped' => false,
            ])
           
            ->add('role', ChoiceType::class, [
                'choices' => [
                    'Admin' => Role::ADMIN,
                    'Responsable de salle' => Role::RESPONSABLE_SALLE,
                    'Entraîneur' => Role::ENTRAINEUR,
                    'Sportif' => Role::SPORTIF,
                ],
                'placeholder' => 'Choisir un rôle'
            ])
            ->add('specialite', TextType::class, [
                'required' => false,
                'label' => 'Spécialité',
                'attr' => ['placeholder' => 'Spécialité de l\'entraîneur'],
                'disabled' => true, // Désactivé par défaut
            ]);
        
        // Ajouter un événement PRE_SET_DATA pour activer 'specialite' si l'utilisateur est un Entraineur
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $user = $event->getData();
            $form = $event->getForm();
        
            if ($user instanceof Entraineur) {
                $form->get('specialite')->setDisabled(false); // Active le champ pour l'Entraîneur
            }
        });
        }
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
