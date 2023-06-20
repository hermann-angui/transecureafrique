<?php

namespace App\Service\Demande;

use App\Entity\Demande;
use App\Repository\DemandeRepository;
use App\Repository\OtpCodeRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DemandeService
{
    private const WEBSITE_URL = "https://transecureafrica.com";
    private const MEDIA_DIR = "/var/www/html/public/frontend/media/";
    private const MONTANT = 100;

    public function __construct(private ContainerInterface $container, private DemandeRepository  $demandeRepository, private OtpCodeRepository  $otpCodeRepository)
    {
    }

    /**
     * @param Demande $demande
     * @return void
     */
    public function save(Demande $demande): void
    {
         $this->demandeRepository->add($demande, true);
    }

    /**
     * @param array $data
     * @param OtpCodeRepository $otpCodeRepository
     * @param DemandeRepository $demandeRepository
     * @return Demande
     * @throws \Exception
     */
    public function create(array $data): Demande
    {
        $demande = new Demande();
        $demande->setReference(strtoupper(uniqid()));
        if (array_key_exists("numero_carte_grise", $data)) $demande->setNumeroCarteGrise(strtoupper($data["numero_carte_grise"]));
        if (array_key_exists("numero_recepisse", $data)) $demande->setNumeroRecepisse(strtoupper($data["numero_recepisse"]));
        $demande->setMontant(self::MONTANT);
        $demande->setNumeroImmatriculation(strtoupper($data["numero_immatriculation"]));
        $demande->setDateDePremiereMiseEnCirulation(new \DateTime($data["date_de_premiere_mise_en_cirulation"]));
        $demande->setDateDEdition(new \DateTime($data["date_d_edition"]));
        $demande->setIdentiteProprietaire(strtoupper($data["identite_proprietaire"]));
        $demande->setIdentiteProprietairePiece(strtoupper($data["identite_proprietaire_piece"]));
        $demande->setMarqueDuVehicule(strtoupper($data["marque_du_vehicule"]));
        $demande->setGenreVehicule(strtoupper($data["genre_vehicule"]));
        $demande->setTypeCommercial(strtoupper($data["type_commercial"]));
        $demande->setCouleurVehicule(strtoupper($data["couleur_vehicule"]));
        $demande->setCarroserieVehicule(strtoupper($data["carroserie_vehicule"]));
        $demande->setEnergieVehicule(strtoupper($data["energie_vehicule"]));
        $demande->setPlacesAssises($data["places_assises"]);
        $demande->setUsageVehicule($data["usage_vehicule"]);
        $demande->setPuissanceFiscale($data["puissance_fiscale"]);
        $demande->setNombreDEssieux($data["nombre_d_essieux"]);
        $demande->setCylindree($data["cylindree"]);
        $demande->setNumeroVinChassis($data["numero_vin_chassis"]);
        $demande->setSocieteDeCredit(strtoupper($data["societe_de_credit"]));
        $demande->setTypeTechnique(strtoupper($data["type_technique"]));
        $demande->setNumeroDImmatriculationPrecedent(strtoupper($data["numero_d_immatriculation_precedent"]));

        $demande->setDateRendezVous(new \DateTime("tomorrow"));

        if (array_key_exists("authid", $data)) {
            $otpCode = $this->otpCodeRepository->find($data["authid"]);
            if ($otpCode && !$demande->getOtpCodes()->contains($otpCode)) {
                $demande->addOtpCode($otpCode);
            }
        }
        $this->demandeRepository->add($demande, true);
        return $demande;
    }

    public function update(?Demande &$demande, array $data): ?Demande
    {
        if (array_key_exists("numero_carte_grise", $data)) $demande->setNumeroCarteGrise(strtoupper($data["numero_carte_grise"]));
        if (array_key_exists("numero_recepisse", $data)) $demande->setNumeroRecepisse(strtoupper($data["numero_recepisse"]));
        $demande->setNumeroImmatriculation(strtoupper($data["numero_immatriculation"]));
        $demande->setDateDePremiereMiseEnCirulation(new \DateTime($data["date_de_premiere_mise_en_cirulation"]));
        $demande->setDateDEdition(new \DateTime($data["date_d_edition"]));
        $demande->setIdentiteProprietaire(strtoupper($data["identite_proprietaire"]));
        $demande->setIdentiteProprietairePiece(strtoupper($data["identite_proprietaire_piece"]));
        $demande->setMarqueDuVehicule(strtoupper($data["marque_du_vehicule"]));
        $demande->setGenreVehicule(strtoupper($data["genre_vehicule"]));
        $demande->setTypeCommercial(strtoupper($data["type_commercial"]));
        $demande->setCouleurVehicule(strtoupper($data["couleur_vehicule"]));
        $demande->setCarroserieVehicule(strtoupper($data["carroserie_vehicule"]));
        $demande->setEnergieVehicule(strtoupper($data["energie_vehicule"]));
        $demande->setPlacesAssises($data["places_assises"]);
        $demande->setUsageVehicule($data["usage_vehicule"]);
        $demande->setPuissanceFiscale($data["puissance_fiscale"]);
        $demande->setNombreDEssieux($data["nombre_d_essieux"]);
        $demande->setCylindree($data["cylindree"]);
        $demande->setNumeroVinChassis($data["numero_vin_chassis"]);
        $demande->setSocieteDeCredit(strtoupper($data["societe_de_credit"]));
        $demande->setTypeTechnique(strtoupper($data["type_technique"]));
        $demande->setNumeroDImmatriculationPrecedent(strtoupper($data["numero_d_immatriculation_precedent"]));

        $demande->setDateRendezVous(new \DateTime("tomorrow"));

        if (array_key_exists("authid", $data)) {
            $otpCode = $this->otpCodeRepository->find($data["authid"]);
            if ($otpCode && !$demande->getOtpCodes()->contains($otpCode)) {
                $demande->addOtpCode($otpCode);
            }
        }
        $this->demandeRepository->add($demande, true);
        return $demande;
    }

    public function getGroupByMarque(){
        return $this->demandeRepository->findGroupByMarque();
    }

    public function getGroupByEnergie(){
        return $this->demandeRepository->findGroupByEnergie();
    }

    public function getTotalPendingPayment(){
        return $this->demandeRepository->findTotaNotPayed();
    }

    public function  getTotalUndeliveredEachMonth(){
        return $this->demandeRepository->findTotalUndeliveredEachMonth();
    }
}
