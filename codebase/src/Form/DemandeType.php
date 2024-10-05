<?php

namespace App\Form;

use App\Entity\Demande;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DemandeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('numero_carte_grise')
            ->add('numero_recepisse')
            ->add('numeroTelephoneProprietaire')
            ->add('numero_immatriculation')
            ->add('date_de_premiere_mise_en_cirulation', DateType::class, [
                'attr' => ['class' => 'js-datepicker'],
                'widget' => 'single_text',
                'html5' => false,
                'mapped' => true,
                'required' => false,
            ])
            ->add('date_d_edition',DateType::class, [
                'attr' => ['class' => 'js-datepicker'],
                'widget' => 'single_text',
                'html5' => false,
                'mapped' => true,
                'required' => false,
            ])
            ->add('identite_proprietaire')
            ->add('identite_proprietaire_piece')
            ->add('marque_du_vehicule',ChoiceType::class, [
                'mapped' => false,
                'attr' => ['class' => 'select2'],
                'required' => false,
                'choices' => $this->marques(),
                'empty_data' => null,
                'data' => null
            ])
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
            ->add('carteGriseImage', FileType::class, [
                'label' => 'Carte grise',
                'mapped' => true,
                'required' => false,
                'data_class' => null
            ])
            ->add('recepisseImage', FileType::class, [
                'label' => 'Recepisse',
                'mapped' => true,
                'required' => false,
                'data_class' => null
            ])
//            ->add('montant')
//            ->add('status')
            ->add('date_rendez_vous', DateType::class, [
                'attr' => ['class' => 'js-datepicker'],
                'widget' => 'single_text',
                'html5' => false,
                'mapped' => true,
                'required' => false,
           ])
         //   ->add('created_at')
         //  ->add('modified_at')
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

    private function marques()
    {
        return [
            "Alfa romeo" => "Alfa romeo",
            "Alpine" => "Alpine",
            "Aston martin" => "Aston martin",
            "Audi" => "Audi",
            "Bentley" => "Bentley",
            "BMW" => "BMW",
            "Chevrolet" => "Chevrolet",
            "Citroen" => "Citroen",
            "Dacia" => "Dacia",
            "DS" => "DS",
            "Ferrari" => "Ferrari",
            "Fiat" => "Fiat",
            "Ford" => "Ford",
            "Honda" => "Honda",
            "Hyndai" => "Hyndai",
            "Infiniti" => "Infiniti",
            "Jaguar" => "Jaguar",
            "Jeep" => "Jeep",
            "Kia" => "Kia",
            "Lamborghini" => "Lamborghini",
            "Land rover" => "Land rover",
            "Lexus" => "Lexus",
            "Lotus" => "Lotus",
            "Maserati" => "Maserati",
            "Mazda" => "Mazda",
            "MCLaren" => "MCLaren",
            "Mercedes" => "Mercedes",
            "Mini" => "Mini",
            "Mitsubishi" => "Mitsubishi",
            "Nissan" => "Nissan",
            "Open" => "Open",
            "Peugeot" => "Peugeot",
            "Porsche" => "Porsche",
            "Renault" => "Renault",
            "Rolls-Royce" => "Rolls-Royce",
            "Seat" => "Seat",
            "Skoda" => "Skoda",
            "Smart" => "Smart",
            "SSangyong" => "SSangyong",
            "Subaru" => "Subaru",
            "Suzuki" => "Suzuki",
            "Tesla" => "Tesla",
            "Toyota" => "Toyota",
            "Volkwagen" => "Volkwagen",
            "Volvo" => "Volvo",
            "Abarth" => "Abarth",
            "Aiways" => "Alfa romeo",
            "Alpina" => "Alfa romeo",
            "Ares design" => "Alfa romeo",
            "Ariel" => "Ariel",
            "Byd" => "Byd",
            "Baw" => "Baw",
            "Bertone" => "Bertone",
            "Bollore" => "Alfa romeo",
            "Brabus" => "Brabus",
            "Brilliance" => "Brilliance",
            "Bugatti" => "Bugatti",
            "Cadillac" => "Cadillac",
            "Caterham" => "Caterham",
            "Changan" => "Changan",
            "Chery" => "Chery",
            "Chrysler" => "Chrysler",
            "Corvette" => "Corvette",
            "Cupra" => "Cupra",
            "Daihatsu" => "Daihatsu",
            "Dallara" => "Dallara",
            "Datsun" => "Datsun",
            "David brown" => "David brown",
            "De tomaso" => "De tomaso",
            "Delorean" => "Delorean",
            "Dodge" => "Dodge",
            "Donkervoort" => "Donkervoort",
            "Drako" => "Drako",
            "Fisker" => "Fisker",
            "GTF" => "GTF",
            "Geely" => "Geely",
            "Genesis" => "Genesis",
            "Ginetta" => "Ginetta",
            "Gordon murray automotive" => "Gordon murray automotive",
            "Hennesey" => "Hennesey",
            "Heuliez" => "Heuliez",
            "Holden" => "Holden",
            "Hummer" => "Hummer",
            "Ineos" => "Ineos",
            "Isuzu" => "Isuzu",
            "Italdesign" => "Italdesign",
            "Iveco" => "Iveco",
            "Karma" => "Karma",
            "Kawasaki" => "Kawasaki",
            "Koenigsegg" => "Koenigsegg",
            "Lada" => "Lada",
            "Lancia" => "Lancia",
            "Leapmotor" => "Leapmotor",
            "Ligier" => "Ligier",
            "Lincoln" => "Lincoln",
            "London Taxi" => "London Taxi",
            "Lucid" => "Lucid",
            "Lynk & Co" => "Lynk & Co",
            "MG" => "MG",
            "Mahindra" => "Mahindra",
            "Maybach" => "Maybach",
            "Micro mobility systems" => "Micro mobility systems",
            "Mobilize" => "Mobilize",
            "Morgan" => "Morgan",
            "Nevs" => "Nevs",
            "Nio" => "Nio",
            "PGO" => "PGO",
            "Pagani" => "Pagani",
            "Pal-v" => "Pal-v",
            "Pariss" => "Pariss",
            "Perana" => "Perana",
            "Pininfarina" => "Pininfarina",
            "Polestar" => "Polestar",
            "Puritalia" => "Puritalia",
            "Qoros" => "Qoros",
            "Ruf" => "Ruf",
            "Radical" => "Radical",
            "Rimac" => "Rimac",
            "Rivian" => "Rivian",
            "Saic" => "Saic",
            "Scg" => "Scg",
            "Saab" => "Saab",
            "Secma" => "Secma",
            "Spyker" => "Spyker",
            "Tvr" => "Tvr",
            "Tata" => "Tata",
            "Techrules" => "Techrules",
            "Think" => "Think",
            "Touring Superleggera" => "Touring Superleggera",
            "Ultima" => "Ultima",
            "Venturi" => "Venturi",
            "Vinfast" => "Vinfast",
            "Voge" => "Voge",
            "Westfield" => "Westfield",
            "Wiesmann" => "Wiesmann",
            "Yamaha" => "Yamaha",
        ];
    }
}
