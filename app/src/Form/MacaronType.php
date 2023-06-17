<?php

namespace App\Form;

use App\DTO\MemberRequestDto;
use App\Entity\Demande;
use App\Entity\Macaron;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Intl\Countries;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MacaronType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $past = new \DateTime('- 65 years');
        $end = new \DateTime('- 18 years');
        $countries = array_combine(
            array_values(Countries::getNames()),
            array_values(Countries::getNames())
        );

        $builder
            ->add('lastName', TextType::class, [
                'label' => 'Nom',
                'mapped' => true,
                'required' => true
            ])
            ->add('firstName', TextType::class, [
                'label' => 'Prénoms',
                'mapped' => true,
                'required' => true
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'mapped' => true,
                'required' => true
            ])
            ->add('company', ChoiceType::class, [
                'label' => 'Compagnie de VTC',
                'mapped' => true,
                'required' => false,
                'choices' => [
                    "YANGO" => "YANGO",
                    "UBER" => "UBER",
                    "HEECTH" => "HEECTH",
                    "LE TRANSPORTEUR" => "LE TRANSPORTEUR",
                    "IZIGO" => "IZIGO"
                ],
                'empty_data' => null,
                'data' => null,
            ])
            ->add('city', ChoiceType::class, [
                'label' => "Ville",
                'mapped' => true,
                'required' => false,
                'choices' => [
                    "ABIDJAN" => "ABIDJAN",
                    "BOUAKE" => "BOUAKE",
                    "YAMOUSSOUKRO" => "YAMOUSSOUKRO",
                    "KORHOGO" => "KORHOGO",
                    "MAN" => "MAN",
                    "SAN-PEDRO" => "SAN-PEDRO",
                    "BASSAM" => "BASSAM",
                    "BONOUA" => "BONOUA",
                    "BONDOUKOU" => "BONDOUKOU"
                ],
                'empty_data' => null,
                'data' => null,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Demande::class,
        ]);
    }
}
