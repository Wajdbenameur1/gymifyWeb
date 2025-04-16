<?php

namespace App\Form;

use App\Entity\Equipe;
use App\Enum\Niveau;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EquipeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Team Name',
            ])
            ->add('imageFile', FileType::class, [
                'label' => 'Team Image',
                'mapped' => false, // Keep unmapped since we’ll process it manually
                'required' => true, // Ensure an image is provided
            ])
            ->add('niveau', EnumType::class, [
                'class' => Niveau::class,
                'choice_label' => fn(Niveau $niveau) => ucfirst(strtolower($niveau->value)),
            ])
            ->add('nombre_membres', IntegerType::class, [
                'label' => 'Number of Members',
                'attr' => ['min' => 0],
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Add New Team',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Equipe::class,
        ]);
    }
}