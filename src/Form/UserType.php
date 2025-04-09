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
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class UserType extends AbstractType
{
    private $emailUniquenessValidator;

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
            ->add('specialite', TextType::class, [
                'label' => 'Spécialité',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Spécialité de l\'entraîneur'
                ],
            ]);
    }

    public function validateEmailUniqueness($email, ExecutionContextInterface $context)
    {
        if ($this->emailUniquenessValidator->validate($email)) {
            $context->buildViolation('L\'email {{ value }} est déjà utilisé.')
                ->setParameter('{{ value }}', $email)
                ->addViolation();
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}