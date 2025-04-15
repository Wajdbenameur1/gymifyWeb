<?php
namespace App\Form;

use App\Entity\Sportif;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
<<<<<<< HEAD
use Symfony\Component\Form\Extension\Core\Type\RepeatedType; // Assurez-vous que c'est bien importé
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
=======
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
>>>>>>> 1e2a521f379c042fb627b82253dcd3e5a8f8a1fc

class SportifType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom',
                'constraints' => [
<<<<<<< HEAD
                    new Assert\NotBlank(['message' => 'Le nom est obligatoire.']),
                    new Assert\Length(['max' => 50]),
                ],
=======
                    new NotBlank(['message' => 'Le nom est requis.']),
                    new Length([
                        'max' => 50,
                        'maxMessage' => 'Le nom ne peut dépasser 50 caractères.'
                    ])
                ],
                'attr' => ['class' => 'form-control', 'data-validate' => 'true']
>>>>>>> 1e2a521f379c042fb627b82253dcd3e5a8f8a1fc
            ])
            ->add('prenom', TextType::class, [
                'label' => 'Prénom',
                'constraints' => [
<<<<<<< HEAD
                    new Assert\NotBlank(['message' => 'Le prénom est obligatoire.']),
                    new Assert\Length(['max' => 50]),
                ],
=======
                    new NotBlank(['message' => 'Le prénom est requis.']),
                    new Length([
                        'max' => 50,
                        'maxMessage' => 'Le prénom ne peut dépasser 50 caractères.'
                    ])
                ],
                'attr' => ['class' => 'form-control', 'data-validate' => 'true']
>>>>>>> 1e2a521f379c042fb627b82253dcd3e5a8f8a1fc
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'constraints' => [
<<<<<<< HEAD
                    new Assert\NotBlank(['message' => 'L\'email est obligatoire.']),
                    new Assert\Email(['message' => 'Adresse email invalide.']),
                ],
=======
                    new NotBlank(['message' => 'L\'email est requis.']),
                    new Email(['message' => 'Veuillez entrer un email valide.'])
                ],
                'attr' => ['class' => 'form-control', 'data-validate' => 'true']
>>>>>>> 1e2a521f379c042fb627b82253dcd3e5a8f8a1fc
            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false,
                'required' => true,
                'first_options' => [
                    'label' => 'Mot de passe',
<<<<<<< HEAD
                    'attr' => ['autocomplete' => 'new-password'],
                ],
                'second_options' => [
                    'label' => 'Confirmer le mot de passe',
                    'attr' => ['autocomplete' => 'new-password'],
                ],
                'invalid_message' => 'Les mots de passe ne correspondent pas.',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le mot de passe est obligatoire.']),
                    new Assert\Length([
                        'min' => 8,
                        'minMessage' => 'Le mot de passe doit contenir au moins 8 caractères.',
                    ]),
                ],
=======
                    'attr' => ['class' => 'form-control', 'autocomplete' => 'new-password', 'data-validate' => 'true']
                ],
                'second_options' => [
                    'label' => 'Confirmer le mot de passe',
                    'attr' => ['class' => 'form-control', 'autocomplete' => 'new-password', 'data-validate' => 'true']
                ],
                'invalid_message' => 'Les mots de passe ne correspondent pas.',
                'constraints' => [
                    new NotBlank(['message' => 'Le mot de passe est requis.']),
                    new Length([
                        'min' => 8,
                        'minMessage' => 'Le mot de passe doit contenir au moins 8 caractères.',
                        'max' => 4096
                    ])
                ]
>>>>>>> 1e2a521f379c042fb627b82253dcd3e5a8f8a1fc
            ])
            ->add('dateNaissance', DateType::class, [
                'label' => 'Date de naissance',
                'widget' => 'single_text',
                'required' => true,
<<<<<<< HEAD
=======
                'constraints' => [
                    new NotBlank(['message' => 'La date de naissance est requise.']),
                    new Callback([$this, 'validateAge'])
                ],
                'attr' => ['class' => 'form-control', 'data-validate' => 'true']
>>>>>>> 1e2a521f379c042fb627b82253dcd3e5a8f8a1fc
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sportif::class,
        ]);
    }
<<<<<<< HEAD
}
=======

    public function validateAge($value, ExecutionContextInterface $context): void
    {
        if (!$value instanceof \DateTimeInterface) {
            return;
        }

        $today = new \DateTime();
        $age = $today->diff($value)->y;

        if ($age < 12) {
            $context->buildViolation('Vous devez avoir au moins 12 ans.')
                ->atPath('dateNaissance')
                ->addViolation();
        }
    }
}
>>>>>>> 1e2a521f379c042fb627b82253dcd3e5a8f8a1fc
