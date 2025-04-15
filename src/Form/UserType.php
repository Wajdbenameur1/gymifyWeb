<?php
<<<<<<< HEAD
 // src/Form/UserType.php
=======
>>>>>>> 1e2a521f379c042fb627b82253dcd3e5a8f8a1fc

namespace App\Form;

use App\Entity\User;
use App\Enum\Role;
use Symfony\Component\Form\AbstractType;
<<<<<<< HEAD
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
=======
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
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
            ->add('imageUrl', FileType::class, [
                'label' => 'Photo de profil',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => [
                            'image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/svg+xml',
                        ],
                        'mimeTypesMessage' => 'Veuillez uploader une image valide.',
                        'maxSizeMessage' => 'L\'image ne doit pas dépasser {{ limit }}.',
                    ]),
                    new Callback(function ($value, ExecutionContextInterface $context) {
                        if ($value) {
                            $extension = strtolower($value->guessExtension());
                            $allowedExtensions = ['jpeg', 'jpg', 'png', 'webp', 'gif', 'svg'];
                            if (!in_array($extension, $allowedExtensions)) {
                                $context->buildViolation('Type de fichier non autorisé.')
                                    ->addViolation();
                            }
                        }
                    }),
                ],
                'attr' => [
                    'accept' => 'image/jpeg,image/png,image/webp,image/gif,image/svg+xml',
                    'class' => 'd-none',
                    'data-validate' => 'true',
                    'id' => 'user_imageUrl'
                ],
            ])
            ->add('nom', TextType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Le nom est requis.']),
                    new Length(['max' => 50]),
                ],
                'attr' => ['class' => 'form-control', 'placeholder' => 'Nom'],
            ])
            ->add('prenom', TextType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Le prénom est requis.']),
                    new Length(['max' => 50]),
                ],
                'attr' => ['class' => 'form-control', 'placeholder' => 'Prénom'],
            ])
            ->add('email', EmailType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Email requis.']),
                    new Email(['message' => 'Email invalide.']),
                ],
                'attr' => ['class' => 'form-control', 'placeholder' => 'Email'],
            ])
            ->add('password', PasswordType::class, [
                'required' => false,
                'constraints' => [
                    new Length(['min' => 8, 'minMessage' => 'Minimum 8 caractères.']),
                ],
                'attr' => ['class' => 'form-control', 'placeholder' => 'Mot de passe'],
>>>>>>> 1e2a521f379c042fb627b82253dcd3e5a8f8a1fc
            ])
            ->add('dateNaissance', DateType::class, [
                'widget' => 'single_text',
                'required' => true,
<<<<<<< HEAD
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
=======
                'constraints' => [
                    new NotBlank(['message' => 'Date de naissance requise.']),
                    new Callback([$this, 'validateAge']),
                ],
                'attr' => ['class' => 'form-control'],
            ])
            ->add('role', ChoiceType::class, [
                'choices' => (function () use ($options) {
                    $currentUserRole = $options['current_user_role'];
                    if ($currentUserRole === Role::RESPONSABLE_SALLE) {
                        return [
                            Role::SPORTIF,
                            Role::ENTRAINEUR,
                        ];
                    }
                    return array_filter(Role::cases(), fn(Role $role) => $role !== Role::UTILISATEUR);
                })(),
                'choice_label' => fn(Role $role) => $role->label(),
                'choice_value' => fn(?Role $role) => $role?->value,
                'placeholder' => 'Sélectionner un rôle',
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Le rôle est requis.']),
                ],
                'attr' => ['class' => 'form-control'],
            ])
            ->add('specialite', TextType::class, [
                'required' => false,
                'constraints' => [
                    new Length(['max' => 100]),
                    new Callback(function ($value, ExecutionContextInterface $context) {
                        $form = $context->getObject()->getParent();
                        $role = $form->get('role')->getData();
                        if ($role === Role::ENTRAINEUR && empty($value)) {
                            $context->buildViolation('La spécialité est requise pour les entraîneurs.')
                                ->addViolation();
                        }
                    }),
                ],
                'attr' => [
                    'id' => 'specialite_field',
                    'class' => 'form-control',
                    'placeholder' => 'Spécialité (pour les entraîneurs)',
                ],
            ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function ($event) {
            $form = $event->getForm();
            $data = $event->getData();

            $hasImage = $data instanceof User && $data->getImageUrl() !== null;

            $form->add('hasImage', TextType::class, [
                'mapped' => false,
                'data' => $hasImage ? '1' : '0',
                'attr' => ['class' => 'd-none']
            ]);
        });
    }

    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['has_image'] = $form->get('hasImage')->getData() === '1';
        $view->children['imageUrl']->vars['attr']['data-upload-method'] = 'both';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'current_user_role' => null,
            'attr' => [
                'id' => 'user-form',
                'class' => 'needs-validation',
                'novalidate' => 'novalidate',
            ],
        ]);
    }

    public function validateAge($value, ExecutionContextInterface $context): void
    {
        if (!$value instanceof \DateTimeInterface) return;

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
