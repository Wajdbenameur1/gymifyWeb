<?php

namespace App\Form;

use App\Entity\CommandeProduit;
use App\Entity\Produit;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommandeProduitType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('produit', EntityType::class, [
                'class' => Produit::class,
                'choice_label' => function (Produit $produit) {
                    return sprintf('%s (€%.2f)', $produit->getNom(), $produit->getPrix());
                },
                'placeholder' => 'Choose a product',
                'required' => true
            ])
            ->add('quantite', NumberType::class, [
                'label' => 'Quantity',
                'attr' => [
                    'min' => 1,
                    'step' => 1
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CommandeProduit::class,
        ]);
    }
} 