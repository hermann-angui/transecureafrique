<?php

namespace App\Service\Demande;

use App\Entity\Demande;
use App\Helper\FileUploadHelper;
use App\Repository\DemandeRepository;
use App\Repository\OtpCodeRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Uid\Uuid;

class DemandeService
{
    private const WEBSITE_URL = "https://transecureafrica.com";
    private const MEDIA_DIR = "/var/www/html/public/frontend/media/";
    private const MONTANT = 100;

    public function __construct(private ContainerInterface $container, private FileUploadHelper $fileUploadHelper, private DemandeRepository  $demandeRepository, private OtpCodeRepository  $otpCodeRepository){}

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
    public function create(array $data): ?Demande
    {
        try{

            $demande = new Demande();
            $demande->setReference($this->generateReference());
            if (array_key_exists("numero_carte_grise", $data)) $demande->setNumeroCarteGrise(strtoupper(trim($data["numero_carte_grise"])));
            if (array_key_exists("numero_recepisse", $data)) $demande->setNumeroRecepisse(strtoupper(trim($data["numero_recepisse"])));
            if(array_key_exists("numero_immatriculation", $data)) $demande->setNumeroImmatriculation(strtoupper(trim($data["numero_immatriculation"])));

            if(array_key_exists("date_de_premiere_mise_en_cirulation", $data)){
                try{
                    $date = \DateTime::createFromFormat("d/m/Y", $data["date_de_premiere_mise_en_cirulation"]);
                    if(!$date) throw new \Exception();
                    $demande->setDateDePremiereMiseEnCirulation($date);
                }catch(\Exception $e){
                    $demande->setDateDePremiereMiseEnCirulation(null);
                }
            }

            if(array_key_exists("date_d_edition", $data)) {
                try{
                    $date = \DateTime::createFromFormat("d/m/Y", $data["date_d_edition"]);
                    if(!$date) throw new \Exception();
                    $demande->setDateDEdition($date);
                }catch(\Exception $e){
                    $demande->setDateDEdition(null);
                }
            }

            if(array_key_exists("identite_proprietaire", $data)) $demande->setIdentiteProprietaire(strtoupper(trim($data["identite_proprietaire"])));
            if(array_key_exists("identite_proprietaire_piece", $data)) $demande->setIdentiteProprietairePiece(strtoupper(trim($data["identite_proprietaire_piece"])));
            if(array_key_exists("marque_du_vehicule", $data)) $demande->setMarqueDuVehicule(strtoupper(trim($data["marque_du_vehicule"])));
            if(array_key_exists("genre_vehicule", $data)) $demande->setGenreVehicule(strtoupper(trim($data["genre_vehicule"])));
            if(array_key_exists("type_commercial", $data)) $demande->setTypeCommercial(strtoupper(trim($data["type_commercial"])));
            if(array_key_exists("couleur_vehicule", $data)) $demande->setCouleurVehicule(strtoupper(trim($data["couleur_vehicule"])));
            if(array_key_exists("carroserie_vehicule", $data)) $demande->setCarroserieVehicule(strtoupper(trim($data["carroserie_vehicule"])));
            if(array_key_exists("energie_vehicule", $data)) $demande->setEnergieVehicule(strtoupper(trim($data["energie_vehicule"])));
            if(array_key_exists("places_assises", $data)) $demande->setPlacesAssises(trim($data["places_assises"]));
            if(array_key_exists("usage_vehicule", $data)) $demande->setUsageVehicule(trim($data["usage_vehicule"]));
            if(array_key_exists("puissance_fiscale", $data)) $demande->setPuissanceFiscale(trim($data["puissance_fiscale"]));
            if(array_key_exists("nombre_d_essieux", $data)) $demande->setNombreDEssieux(trim($data["nombre_d_essieux"]));
            if(array_key_exists("cylindree", $data)) $demande->setCylindree(trim($data["cylindree"]));
            if(array_key_exists("numero_vin_chassis", $data)) $demande->setNumeroVinChassis(strtoupper(trim($data["numero_vin_chassis"])));
            if(array_key_exists("societe_de_credit", $data)) $demande->setSocieteDeCredit(strtoupper(trim($data["societe_de_credit"])));
            if(array_key_exists("type_technique", $data))  $demande->setTypeTechnique(strtoupper(trim($data["type_technique"])));
            if(array_key_exists("numero_d_immatriculation_precedent", $data))  $demande->setNumeroDImmatriculationPrecedent(strtoupper(trim($data["numero_d_immatriculation_precedent"])));

            if(array_key_exists("macaron_qrcode_number", $data))  $demande->setMacaronQrcodeNumber(strtoupper(trim($data["macaron_qrcode_number"])));
            if(array_key_exists("numero_telephone_proprietaire", $data))  $demande->setNumeroTelephoneProprietaire(strtoupper(trim($data["numero_telephone_proprietaire"])));


            $demande->setMontant(self::MONTANT);

            $appointmentDate = new \DateTime();
            $appointmentDate->modify("+2 day");
            $demande->setDateRendezVous($appointmentDate);

            $otpCode = $this->otpCodeRepository->find($data["otpcode"]);
            $demande->addOptcode($otpCode);

            /*
            if (array_key_exists("authid", $data)) {
                $otpCode = $this->otpCodeRepository->find($data["authid"]);
                if ($otpCode && !$demande->getOtpCode()) {
                    $demande->setOtpCode($otpCode);
                }
            }
            */
            $this->demandeRepository->add($demande, true);
            return $demande;
        }catch (\Exception $e){
            return ["error" => $e->getMessage()];
        }
    }

    public function update(?Demande &$demande, array $data): ?Demande
    {
        if(empty($data)) return null;
        if (array_key_exists("numero_carte_grise", $data)) $demande->setNumeroCarteGrise(strtoupper($data["numero_carte_grise"]));
        if (array_key_exists("numero_recepisse", $data)) $demande->setNumeroRecepisse(strtoupper($data["numero_recepisse"]));
        if(array_key_exists("numero_immatriculation", $data)) $demande->setNumeroImmatriculation(strtoupper($data["numero_immatriculation"]));

        if(array_key_exists("date_de_premiere_mise_en_cirulation", $data)){
            try{
                $date = \DateTime::createFromFormat("d/m/Y",$data["date_de_premiere_mise_en_cirulation"]);
                if(!$date) throw new \Exception();
                $demande->setDateDePremiereMiseEnCirulation($date);
            }catch(\Exception $e){
                $demande->setDateDePremiereMiseEnCirulation(null);
            }
        }

        if(array_key_exists("date_d_edition", $data)) {
            try{
                $date = \DateTime::createFromFormat("d/m/Y", $data["date_d_edition"]);
                if(!$date) throw new \Exception();
                $demande->setDateDEdition($date);
            }catch(\Exception $e){
                $demande->setDateDEdition(null);
            }
        }

        if(array_key_exists("identite_proprietaire", $data)) $demande->setIdentiteProprietaire(strtoupper(trim($data["identite_proprietaire"])));
        if(array_key_exists("identite_proprietaire_piece", $data)) $demande->setIdentiteProprietairePiece(strtoupper(trim($data["identite_proprietaire_piece"])));
        if(array_key_exists("marque_du_vehicule", $data)) $demande->setMarqueDuVehicule(strtoupper(trim($data["marque_du_vehicule"])));
        if(array_key_exists("genre_vehicule", $data)) $demande->setGenreVehicule(strtoupper(trim($data["genre_vehicule"])));
        if(array_key_exists("type_commercial", $data)) $demande->setTypeCommercial(strtoupper(trim($data["type_commercial"])));
        if(array_key_exists("couleur_vehicule", $data)) $demande->setCouleurVehicule(strtoupper(trim($data["couleur_vehicule"])));
        if(array_key_exists("carroserie_vehicule", $data)) $demande->setCarroserieVehicule(strtoupper(trim($data["carroserie_vehicule"])));
        if(array_key_exists("energie_vehicule", $data)) $demande->setEnergieVehicule(strtoupper(trim($data["energie_vehicule"])));
        if(array_key_exists("places_assises", $data)) $demande->setPlacesAssises(trim($data["places_assises"]));
        if(array_key_exists("usage_vehicule", $data)) $demande->setUsageVehicule(trim($data["usage_vehicule"]));
        if(array_key_exists("puissance_fiscale", $data)) $demande->setPuissanceFiscale(trim($data["puissance_fiscale"]));
        if(array_key_exists("nombre_d_essieux", $data)) $demande->setNombreDEssieux(trim($data["nombre_d_essieux"]));
        if(array_key_exists("cylindree", $data)) $demande->setCylindree(trim($data["cylindree"]));
        if(array_key_exists("numero_vin_chassis", $data)) $demande->setNumeroVinChassis(strtoupper(trim($data["numero_vin_chassis"])));
        if(array_key_exists("societe_de_credit", $data)) $demande->setSocieteDeCredit(strtoupper(trim($data["societe_de_credit"])));
        if(array_key_exists("type_technique", $data))  $demande->setTypeTechnique(strtoupper(trim($data["type_technique"])));
        if(array_key_exists("numero_d_immatriculation_precedent", $data))  $demande->setNumeroDImmatriculationPrecedent(strtoupper(trim($data["numero_d_immatriculation_precedent"])));

        if(array_key_exists("macaron_qrcode_number", $data))  $demande->setMacaronQrcodeNumber(strtoupper(trim($data["macaron_qrcode_number"])));
        if(array_key_exists("numero_telephone_proprietaire", $data))  $demande->setNumeroTelephoneProprietaire(strtoupper(trim($data["numero_telephone_proprietaire"])));


        if(array_key_exists("recepisse_image", $data)) {
            if(!empty($data["recepisse_image"])){
                $fileName = $this->fileUploadHelper->upload($data["recepisse_image"], self::MEDIA_DIR);
                if($fileName) $demande->setRecepisseImage($fileName->getFilename());
            }
        }
        if(array_key_exists("carte_grise_image", $data)) {
            if(!empty($data["carte_grise_image"])){
                $fileName = $this->fileUploadHelper->upload($data["carte_grise_image"], self::MEDIA_DIR);
                if($fileName) $demande->setRecepisseImage($fileName->getFilename());
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
