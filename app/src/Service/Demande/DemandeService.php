<?php

namespace App\Service\Demande;

use App\Entity\Demande;
use App\Repository\DemandeRepository;
use App\Repository\OtpCodeRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Uid\Uuid;

class DemandeService
{
    private const WEBSITE_URL = "https://transecureafrica.com";
    private const MEDIA_DIR = "/var/www/html/public/frontend/media/";
    private const MONTANT = 100;

    public function __construct(private ContainerInterface $container, private DemandeRepository  $demandeRepository, private OtpCodeRepository  $otpCodeRepository){}

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
        $demande->setReference($this->generateReference());
        if (array_key_exists("numero_carte_grise", $data)) $demande->setNumeroCarteGrise(strtoupper($data["numero_carte_grise"]));
        if (array_key_exists("numero_recepisse", $data)) $demande->setNumeroRecepisse(strtoupper($data["numero_recepisse"]));
        if(array_key_exists("numero_immatriculation", $data)) $demande->setNumeroImmatriculation(strtoupper($data["numero_immatriculation"]));
        if(array_key_exists("date_de_premiere_mise_en_cirulation", $data)) $demande->setDateDePremiereMiseEnCirulation(new \DateTime($data["date_de_premiere_mise_en_cirulation"]));
        if(array_key_exists("date_d_edition", $data)) $demande->setDateDEdition(new \DateTime($data["date_d_edition"]));
        if(array_key_exists("identite_proprietaire", $data)) $demande->setIdentiteProprietaire(strtoupper($data["identite_proprietaire"]));
        if(array_key_exists("identite_proprietaire_piece", $data)) $demande->setIdentiteProprietairePiece(strtoupper($data["identite_proprietaire_piece"]));
        if(array_key_exists("marque_du_vehicule", $data)) $demande->setMarqueDuVehicule(strtoupper($data["marque_du_vehicule"]));
        if(array_key_exists("genre_vehicule", $data)) $demande->setGenreVehicule(strtoupper($data["genre_vehicule"]));
        if(array_key_exists("type_commercial", $data)) $demande->setTypeCommercial(strtoupper($data["type_commercial"]));
        if(array_key_exists("couleur_vehicule", $data)) $demande->setCouleurVehicule(strtoupper($data["couleur_vehicule"]));
        if(array_key_exists("carroserie_vehicule", $data)) $demande->setCarroserieVehicule(strtoupper($data["carroserie_vehicule"]));
        if(array_key_exists("energie_vehicule", $data)) $demande->setEnergieVehicule(strtoupper($data["energie_vehicule"]));
        if(array_key_exists("places_assises", $data)) $demande->setPlacesAssises($data["places_assises"]);
        if(array_key_exists("usage_vehicule", $data)) $demande->setUsageVehicule($data["usage_vehicule"]);
        if(array_key_exists("puissance_fiscale", $data)) $demande->setPuissanceFiscale($data["puissance_fiscale"]);
        if(array_key_exists("nombre_d_essieux", $data)) $demande->setNombreDEssieux($data["nombre_d_essieux"]);
        if(array_key_exists("cylindree", $data)) $demande->setCylindree($data["cylindree"]);
        if(array_key_exists("numero_vin_chassis", $data)) $demande->setNumeroVinChassis($data["numero_vin_chassis"]);
        if(array_key_exists("societe_de_credit", $data)) $demande->setSocieteDeCredit(strtoupper($data["societe_de_credit"]));
        if(array_key_exists("type_technique", $data))  $demande->setTypeTechnique(strtoupper($data["type_technique"]));
        if(array_key_exists("numero_d_immatriculation_precedent", $data))  $demande->setNumeroDImmatriculationPrecedent(strtoupper($data["numero_d_immatriculation_precedent"]));
        $demande->setMontant(self::MONTANT);

        $appointmentDate = new \DateTime();
        $appointmentDate->modify("+2 day");
//        $exit = false;
//        do {
//            $count = $this->demandeRepository->count(['date_rendez_vous'=> $appointmentDate]);
//            if($count <= 50) $exit = true;
//            if($exit) $appointmentDate->modify("+1 day");
//        } while(!$exit);

        $demande->setDateRendezVous($appointmentDate);

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
        return $this->demandeRepository->findTotalNotPayed();
    }

    public function  getTotalUndeliveredEachMonth(){
        return $this->demandeRepository->findTotalUndeliveredEachMonth();
    }

    public function generateReference() {
        $now = new \DateTime();
        $year = $now->format("Y");
        return $year . strtoupper(substr(Uuid::v4()->toRfc4122(), 0, 6));
    }

    public function scheduleAppointment()
    {
        $appointmentDate = new \DateTime("now");
        $appointmentDate->modify("+1 day");
        do {
            $count = $this->demandeRepository->count(['date_rendez_vous'=> $appointmentDate]);
            $exit = ($count <= 50) ? true : false;
            if(!$exit) $appointmentDate->modify("+1 day");
        } while(!$exit);

        return $appointmentDate;
    }
}
