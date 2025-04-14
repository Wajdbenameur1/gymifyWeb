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
                'label' => 'Type d\'abonnement'
            ])
            ->add('tarif', NumberType::class, [
                'label' => 'Tarif (DT)'
            ])
            ->add('activite', EntityType::class, [
                'class' => Activité::class,
                'choice_label' => 'nom',
                'label' => 'Activité',
                'choices' => $options['activites'],
            ])
            ->add('salle', EntityType::class, [
                'class' => Salle::class,
                'choice_label' => 'nom',
                'label' => 'Salle',
                'disabled' => true, // Optionnel : désactiver si vous voulez juste afficher la salle prédéfinie
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