<?php

namespace App\Form;

use App\Entity\Macaron;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MacaronType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $builder
            ->add('reference', TextType::class, [
                'label' => 'Reference',
                'mapped' => true,
                'required' => true
            ])
            ->add('status', TextType::class, [
                'label' => 'Statut',
                'mapped' => true,
                'required' => true
            ])
            ->add('macaronQrcodeNumber', TextType::class, [
                'label' => 'N° QR Code',
                'mapped' => true,
                'required' => true
            ])
            ->add('validityFrom', DateType::class, [
                'label' => 'Valid',
                'mapped' => true,
                'required' => true,
                'attr' => ['class' => 'js-datepicker'],
                'widget' => 'single_text',
                'html5' => false,
                'format' => 'dd/MM/yyyy',
            ])
            ->add('validityTo', DateType::class, [
                'label' => 'Nom',
                'attr' => ['class' => 'js-datepicker'],
                'widget' => 'single_text',
                'html5' => false,
                'mapped' => true,
                'required' => false,
                'format' => 'dd/MM/yyyy',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Macaron::class,
        ]);
    }
}
