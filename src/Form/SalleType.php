<?php

namespace App\Form;

use App\Entity\Salle;
use App\Entity\ResponsableSalle;
use App\Enum\Role;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class SalleType extends AbstractType
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $currentSalleId = $options['current_salle_id'] ?? null;

        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom de la Salle*',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le nom est obligatoire']),
                    new Assert\Length([
                        'max' => 200,
                        'maxMessage' => 'Le nom ne doit pas dépasser {{ limit }} caractères'
                    ]),
                    new Assert\Callback([$this, 'validateUniqueSalleName'])
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Nom unique de la salle'
                ]
            ])
            ->add('adresse', TextType::class, [
                'label' => 'Adresse*',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'L\'adresse est obligatoire'])
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Adresse complète'
                ]
            ])
            ->add('num_tel', TelType::class, [
                'label' => 'Numéro de téléphone*',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le numéro est obligatoire']),
                    new Assert\Regex([
                        'pattern' => '/^\+216\s\d{2}\s\d{3}\s\d{3}$/',
                        'message' => 'Le format doit être: +216 XX XXX XXX'
                    ])
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => '+216 XX XXX XXX',
                    'pattern' => '\+216\s\d{2}\s\d{3}\s\d{3}'
                ]
            ])
            ->add('details', TextareaType::class, [
                'label' => 'Détails',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 4,
                    'placeholder' => 'Description de la salle...'
                ]
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email*',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'L\'email est obligatoire']),
                    new Assert\Email(['message' => 'L\'email "{{ value }}" n\'est pas valide'])
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'email@example.com'
                ]
            ])
            ->add('image', FileType::class, [
                'label' => 'Image de la salle',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new Assert\File([
                        'maxSize' => '2M',
                        'mimeTypes' => ['image/jpeg', 'image/png', 'image/webp'],
                        'mimeTypesMessage' => 'Veuillez uploader une image valide (JPEG, PNG ou WebP)'
                    ])
                ],
                'attr' => ['class' => 'form-control-file']
            ])
            ->add('responsable', EntityType::class, [
                'class' => ResponsableSalle::class,
                'choice_label' => function(ResponsableSalle $responsable) {
                    return $responsable->getNom() . ' ' . $responsable->getPrenom();
                },
                'query_builder' => function () use ($currentSalleId) {
                    $qb = $this->entityManager->getRepository(ResponsableSalle::class)
                        ->createQueryBuilder('r')
                        ->where('r.role = :role')
                        ->setParameter('role', Role::RESPONSABLE_SALLE)
                        ->leftJoin('r.salle', 's'); // Jointure explicite

                    if ($currentSalleId) {
                        // Inclure le responsable actuel de la salle ou les responsables non associés
                        $qb->andWhere('s.id IS NULL OR s.id = :currentSalleId')
                           ->setParameter('currentSalleId', $currentSalleId);
                    } else {
                        // Exclure tous les responsables associés à une salle
                        $qb->andWhere('s.id IS NULL');
                    }

                    return $qb;
                },
                'label' => 'Responsable de la salle*',
                'placeholder' => 'Choisir un responsable',
                'required' => true,
                'attr' => ['class' => 'form-control']
            ]);
    }

    public function validateUniqueSalleName($value, ExecutionContextInterface $context)
    {
        $existingSalle = $this->entityManager->getRepository(Salle::class)
            ->findOneBy(['nom' => $value]);

        // Récupérer l'entité Salle à partir des données du formulaire
        $salle = $context->getRoot()->getData();

        // Vérifier si une salle avec ce nom existe déjà et si elle est différente de la salle actuelle
        if ($existingSalle && (!$salle->getId() || $existingSalle->getId() !== $salle->getId())) {
            $context->buildViolation('Ce nom de salle est déjà utilisé')
                ->atPath('nom')
                ->addViolation();
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Salle::class,
            'current_salle_id' => null,
        ]);
    }
}