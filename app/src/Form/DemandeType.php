<?php

namespace App\Form;

use App\Entity\Demande;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DemandeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('numero_carte_grise')
            ->add('numero_recepisse')
            ->add('numero_immatriculation')
            ->add('date_de_premiere_mise_en_cirulation')
            ->add('date_d_edition')
            ->add('identite_proprietaire')
            ->add('identite_proprietaire_piece')
            ->add('marque_du_vehicule')
            ->add('genre_vehicule')
            ->add('type_commercial')
            ->add('couleur_vehicule')
            ->add('carroserie_vehicule')
            ->add('energie_vehicule')
            ->add('places_assises')
            ->add('usage_vehicule')
            ->add('puissance_fiscale')
            ->add('nombre_d_essieux')
            ->add('cylindree')
            ->add('numero_vin_chassis')
            ->add('societe_de_credit')
            ->add('type_technique')
            ->add('numero_d_immatriculation_precedent')
            ->add('reference')
            ->add('montant')
            ->add('qrcode')
            ->add('type')
            ->add('payment_type')
            ->add('status')
            ->add('date_rendez_vous')
            ->add('created_at')
            ->add('modified_at')
           // ->add('payment')
           // ->add('macaron')
           // ->add('otpcode')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Demande::class,
        ]);
    }
}