<?php

namespace App\Form;

use App\Entity\Payment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PaymentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('reference',TextType::class, [
                'label' => 'Référence',
                'mapped' => true,
                'required' => true
            ])
            ->add('montant',TextType::class, [
                'label' => 'Montant',
                'mapped' => true,
                'required' => true
            ])
            ->add('status',TextType::class, [
                'label' => 'Status',
                'mapped' => true,
                'required' => true
            ])
            ->add('operateur',TextType::class, [
                'label' => 'Operateur',
                'mapped' => true,
                'required' => true
            ])
            ->add('code_payment_operateur',TextType::class, [
                'label' => 'Code paiement',
                'mapped' => true,
                'required' => true
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
