<?php
namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Le nom est requis.']),
                    new Length(['max' => 50, 'maxMessage' => 'Le nom ne peut dépasser 50 caractères.'])
                ],
                'attr' => ['class' => 'form-control', 'data-validate' => 'true']
            ])
            ->add('prenom', TextType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Le prénom est requis.']),
                    new Length(['max' => 50, 'maxMessage' => 'Le prénom ne peut dépasser 50 caractères.'])
                ],
                'attr' => ['class' => 'form-control', 'data-validate' => 'true']
            ])
            ->add('email', EmailType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'L\'email est requis.']),
                    new Email(['message' => 'Veuillez entrer un email valide.'])
                ],
                'attr' => ['class' => 'form-control', 'data-validate' => 'true']
            ])
            ->add('password', PasswordType::class, [
                'required' => false,
                'constraints' => [
                    new Length([
                        'min' => 8,
                        'minMessage' => 'Le mot de passe doit contenir au moins 8 caractères.',
                        'max' => 4096
                    ])
                ],
                'attr' => ['class' => 'form-control', 'data-validate' => 'true']
            ])
            ->add('dateNaissance', DateType::class, [
                'widget' => 'single_text',
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'La date de naissance est requise.']),
                    new Callback([$this, 'validateAge'])
                ],
                'attr' => ['class' => 'form-control', 'data-validate' => 'true']
            ])
            ->add('role', ChoiceType::class, [
                'choices' => [
                    'Sportif' => 'sportif',
                    'Entraîneur' => 'entraineur',
                    'Responsable de salle' => 'responsable_salle',
                    'Admin' => 'admin'
                ],
                'placeholder' => 'Sélectionner un rôle',
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Le rôle est requis.'])
                ],
                'attr' => ['class' => 'form-control', 'data-validate' => 'true']
            ])
            ->add('specialite', TextType::class, [
                'required' => false,
                'constraints' => [
                    new Length(['max' => 100, 'maxMessage' => 'La spécialité ne peut dépasser 100 caractères.']),
                    new Callback(function ($value, ExecutionContextInterface $context) {
                        $form = $context->getObject()->getParent();
                        $role = $form->get('role')->getData();
                        if ($role === 'entraineur' && empty($value)) {
                            $context->buildViolation('La spécialité est requise pour les entraîneurs.')
                                ->addViolation();
                        }
                    })
                ],
                'attr' => ['id' => 'specialite_field', 'class' => 'form-control', 'data-validate' => 'true']
            ])
            ->add('imageUrl', FileType::class, [
                'label' => 'Image de profil',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/webp',
                            'image/gif',
                            'image/svg+xml'
                        ],
                        'mimeTypesMessage' => 'Veuillez uploader une image valide (JPEG, PNG, WebP, GIF ou SVG).',
                        'maxSizeMessage' => 'L\'image ne doit pas dépasser {{ limit }} ({{ suffix }}).',
                        'uploadIniSizeErrorMessage' => 'L\'image ne doit pas dépasser {{ limit }} ({{ suffix }}).',
                        'uploadFormSizeErrorMessage' => 'L\'image ne doit pas dépasser {{ limit }} ({{ suffix }}).',
                        'uploadErrorMessage' => 'Une erreur est survenue lors de l\'upload de l\'image.'
                    ]),
                    new Callback(function ($value, ExecutionContextInterface $context) {
                        if ($value) {
                            $extension = strtolower($value->guessExtension());
                            $allowedExtensions = ['jpeg', 'jpg', 'png', 'webp', 'gif', 'svg'];
                            
                            if (!in_array($extension, $allowedExtensions)) {
                                $context->buildViolation('Le type de fichier n\'est pas autorisé. Types autorisés: '.implode(', ', $allowedExtensions))
                                    ->addViolation();
                            }
                        }
                    })
                ],
                'attr' => [
                    'accept' => 'image/jpeg,image/png,image/webp,image/gif,image/svg+xml',
                    'class' => 'form-control d-none',
                    'data-validate' => 'true',
                    'data-preview-target' => '#image-preview',
                    'data-remove-target' => '#remove-image'
                ],
                'help' => 'Formats acceptés: JPEG, PNG, WebP, GIF, SVG (max 2MB)',
                'help_attr' => ['class' => 'text-muted small']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'attr' => ['id' => 'user-form', 'class' => 'needs-validation', 'novalidate' => 'novalidate']
        ]);
    }

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