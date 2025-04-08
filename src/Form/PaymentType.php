<?php

namespace App\Form;

use App\Entity\Commande;
use App\Entity\Payment;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PaymentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('commande', EntityType::class, [
                'class' => Commande::class,
                'choice_label' => function (Commande $commande) {
                    return sprintf('Order #%d - %s', $commande->getId(), $commande->getTotal());
                },
                'placeholder' => 'Choose an order'
            ])
            ->add('amount', MoneyType::class, [
                'label' => 'Amount',
                'currency' => 'EUR'
            ])
            ->add('payment_method', ChoiceType::class, [
                'label' => 'Payment Method',
                'choices' => [
                    'Credit Card' => 'credit_card',
                    'PayPal' => 'paypal',
                    'Bank Transfer' => 'bank_transfer',
                    'Cash' => 'cash'
                ]
            ])
            ->add('status', ChoiceType::class, [
                'choices' => [
                    'Pending' => 'pending',
                    'Processing' => 'processing',
                    'Completed' => 'completed',
                    'Failed' => 'failed',
                    'Refunded' => 'refunded'
                ]
            ])
            ->add('payment_date', DateTimeType::class, [
                'label' => 'Payment Date',
                'widget' => 'single_text'
            ])
            ->add('transaction_id', TextType::class, [
                'label' => 'Transaction ID',
                'required' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Payment::class,
        ]);
    }
} 