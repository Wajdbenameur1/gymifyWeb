<?php
 // src/Form/UserType.php

namespace App\Form;

use App\Entity\User;
use App\Enum\Role;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class UserType extends AbstractType
{
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
            ])
            ->add('dateNaissance', DateType::class, [
                'widget' => 'single_text',
                'required' => true,

                'constraints' => [
                    new NotBlank(['message' => 'Date de naissance requise.']),
                    new Callback([$this, 'validateAge']),
                ],
                'attr' => ['class' => 'form-control'],
            ])
            ->add('role', ChoiceType::class, [
                'choices' => [
                    'Sportif' => Role::SPORTIF,
                    'Admin' => Role::ADMIN,
                    'Responsable Salle' => Role::RESPONSABLE_SALLE,
                    'Entraineur' => Role::ENTRAINEUR,
                ],
                'required' => true,
                'choice_label' => function ($choice, $key, $value) {
                    return $choice->value; // This will return the string value of the enum
                },
            ])
            
            ->add('specialite', TextType::class, [

            ]);

        if ($options['is_entraineur']) {
            $builder->add('specialite', TextType::class, [
                'label' => 'Spécialité',

                'required' => false,
                'constraints' => [new Assert\Length(['max' => 100])],
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'is_entraineur' => false,
        ]);
    }
}