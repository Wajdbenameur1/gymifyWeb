<?php

namespace App\Form;

use App\Entity\Events;
use App\Enum\EventType;
use App\Enum\Reward;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom')
            ->add('imageFile', FileType::class, [
                'label' => 'Event Image',
                'mapped' => false,
                'required' => true,
            ])
            ->add('date', DateType::class, [
                'widget' => 'single_text',
            ])
            ->add('heure_debut', TimeType::class, [
                'widget' => 'single_text',
            ])
            ->add('heure_fin', TimeType::class, [
                'widget' => 'single_text',
            ])
            ->add('type', EnumType::class, [
                'class' => EventType::class,
                'choice_label' => fn(EventType $type) => ucfirst($type->value),
            ])
            ->add('reward', EnumType::class, [
                'class' => Reward::class,
                'choice_label' => fn(Reward $reward) => ucfirst($reward->value),
            ])
            ->add('description', TextareaType::class)
            ->add('lieu')
            ->add('latitude', HiddenType::class, [
                'required' => false,
            ])
            ->add('longitude', HiddenType::class, [
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Events::class,
        ]);
    }
}