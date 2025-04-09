<?php

namespace App\Form;

use App\Entity\User;
use App\Enum\Role;
use App\Service\EmailUniquenessValidator;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class UserType extends AbstractType
{
    private EmailUniquenessValidator $emailUniquenessValidator;

    public function __construct(EmailUniquenessValidator $emailUniquenessValidator)
    {
        $this->emailUniquenessValidator = $emailUniquenessValidator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', TextType::class, [
                'label' => 'Email',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'L\'email est obligatoire.']),
                    new Assert\Email(['message' => 'Veuillez entrer une adresse email valide.']),
                ],
            ])
            ->add('password', PasswordType::class, [
                'label' => 'Mot de passe',
                'mapped' => false,
                'required' => true,
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le mot de passe est obligatoire.']),
                    new Assert\Length(['min' => 8, 'minMessage' => 'Le mot de passe doit contenir au moins 8 caractères.']),
                ],
            ])
            ->add('nom', TextType::class, [
                'label' => 'Nom',
                'constraints' => [new Assert\NotBlank(), new Assert\Length(['max' => 50])],
            ])
            ->add('prenom', TextType::class, [
                'label' => 'Prénom',
                'constraints' => [new Assert\NotBlank(), new Assert\Length(['max' => 50])],
            ])
            ->add('role', ChoiceType::class, [
                'choices' => [
                    'Admin' => Role::ADMIN,
                    'Responsable Salle' => Role::RESPONSABLE_SALLE,
                    'Entraîneur' => Role::ENTRAINEUR,
                    'Sportif' => Role::SPORTIF,
                ],
                'label' => 'Rôle',
                'constraints' => [new Assert\NotBlank()],
                'attr' => ['id' => 'user_role'],
            ])
            ->add('dateNaissance', DateType::class, [
                'widget' => 'single_text',
                'required' => true,
            ])
            ->add('imageUrl', FileType::class, [
                'label' => 'Photo de profil',
                'required' => false,
                'mapped' => false,
                'constraints' => [
                    new Assert\File([
                        'maxSize' => '2M',
                        'mimeTypes' => ['image/jpeg', 'image/png'],
                        'mimeTypesMessage' => 'Veuillez importer une image JPG ou PNG',
                    ]),
                ],
            ])

        // Ajout dynamique du champ "specialite" si rôle = ENTRAINEUR (utile pour l’édition)
        ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $form = $event->getForm();
            $data = $event->getData();

            // Si le rôle est "Entraîneur", ajouter le champ spécialité
            if ($data && $data->getRole() === 'entraineur') {
                $form->add('specialite');
            }
        });

        // Pour gestion côté création (nouvel utilisateur)
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();
            $form = $event->getForm();

            if (isset($data['role']) && $data['role'] === Role::ENTRAINEUR) {
                $form->add('specialite', TextType::class, [
                    'label' => 'Spécialité',
                    'required' => true,
                    'attr' => ['placeholder' => 'Spécialité de l\'entraîneur', 'id' => 'user_specialite'],
                    'constraints' => [
                        new Assert\NotBlank(['message' => 'La spécialité est obligatoire pour un entraîneur.']),
                    ],
                ]);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
