<?php

namespace App\Form;

use App\Entity\Abonnement;
use App\Entity\Activité;
use App\Entity\Salle;
use App\Enum\TypeAbonnement;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AbonnementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', EnumType::class, [
                'class' => TypeAbonnement::class,
                'choice_label' => 'label',
                'label' => 'Type d\'abonnement*',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le type d\'abonnement est requis.'])
                ],
                'attr' => ['class' => 'form-control']
            ])
            ->add('tarif', NumberType::class, [
                'label' => 'Tarif (DT)*',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le tarif est requis.']),
                    new Assert\GreaterThan([
                        'value' => 0,
                        'message' => 'Le tarif doit être supérieur à 0.'
                    ])
                ],
                'attr' => ['class' => 'form-control', 'placeholder' => 'Entrez le tarif']
            ])
            ->add('activite', EntityType::class, [
                'class' => Activité::class,
                'choice_label' => 'nom',
                'label' => 'Activité*',
                'choices' => $options['activites'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'L\'activité est requise.'])
                ],
                'attr' => ['class' => 'form-control']
            ])
            ->add('salle', EntityType::class, [
                'class' => Salle::class,
                'choice_label' => 'nom',
                'label' => 'Salle',
                'disabled' => true,
                'attr' => ['class' => 'form-control']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Abonnement::class,
            'activites' => [],
        ]);
    }
}