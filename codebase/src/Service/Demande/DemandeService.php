<?php

namespace App\Service\Demande;

use App\Entity\Demande;
use App\Entity\Payment;
use App\Helper\FileUploadHelper;
use App\Helper\PdfGenerator;
use App\Repository\DemandeRepository;
use App\Repository\MacaronRepository;
use App\Repository\OtpCodeRepository;
use App\Repository\PaymentRepository;
use App\Service\Logger\ActivityLogger;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Uid\Uuid;

class DemandeService
{
    private const WEBSITE_URL = "https://transecureafrica.com";
    private const MEDIA_DIR = "/var/www/html/public/frontend/media/";
    private const MONTANT = 10100;

    public function __construct(private FileUploadHelper $fileUploadHelper,
                                private PdfGenerator     $pdfGenerator,
                                private ActivityLogger $activityLogger,
                                private DemandeRepository  $demandeRepository,
                                private MacaronRepository  $macaronRepository,
                                private OtpCodeRepository  $otpCodeRepository){}

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
     * @return Demande|array
     * @throws \Exception
     */
    public function create(array $data): Demande|array|null
    {
        try{
            // Rechercher si existant avec carte grise, recepisse, immatriculation
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

            if(array_key_exists("group_id", $data)) {
                $demande->setGroupe(true);
                $demande->setGroupeId($data['group_id']);
            }

            $otpCode = $this->otpCodeRepository->find($data["otpcode"]);
            $demande->setOtpCode($otpCode);

            $this->save($demande);

            $this->activityLogger->create($demande, 'admin/demande/logs/_create.html.twig');

            return $demande;
        }catch (UniqueConstraintViolationException $e){
            return ["error" => $e->getMessage() , "type" => "Duplication"];
        }catch (\Exception $e){
            return null;
        }
    }

    public function update(?Demande &$demande, array $data): Demande|string|null
    {
        try{
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

            if(array_key_exists("macaron_qrcode_number", $data)) {
                $demande->setMacaronQrcodeNumber(strtoupper(trim($data["macaron_qrcode_number"])));
                $macaron = $demande->getMacaron();
                if($macaron){
                    $macaron->setMacaronQrcodeNumber(strtoupper(trim($data["macaron_qrcode_number"])));
                    $this->macaronRepository->add($macaron, true);
                }
            }

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
                    if($fileName) $demande->setCarteGriseImage($fileName->getFilename());
                }
            }

            $demande->setModifiedAt(new \DateTime('now'));

            $this->save($demande);

            $this->activityLogger->update($demande, 'admin/demande/logs/_update.html.twig');

            return $demande;
        }catch (UniqueConstraintViolationException $e){
            $keys = array_keys($data);
            return $keys[0];
        }catch (\Exception $e){
            return null;
        }
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
        $year = $now->format("y");
        return $year . strtoupper(substr(Uuid::v4()->toRfc4122(), 0, 8));
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

    public function checkDuplicateEntry($data, PaymentRepository $paymentRepository)
    {
        $doublons = [
            "numero_carte_grise" => null,
            "numero_immatriculation" => null,
            "numero_recepisse" => null,
            "numero_vin_chassis" => null,
        ];
        if(array_key_exists("numero_recepisse", $data) && array_key_exists("numero_vin_chassis", $data)){
            $demande = $this->demandeRepository->findOneByNumeroRecepisseOrNumeroVinChassis(
                $data['numero_recepisse'],
                $data['numero_vin_chassis'],
            );
            if($demande && in_array($demande->getStatus(), ['CLOSED', 'PAYE'])) {
                if ($demande->getNumeroCarteGrise() === $data['numero_recepisse'] && !empty($data['numero_recepisse'])) $doublons["numero_recepisse"] = $demande->getNumeroCarteGrise();
                if($demande->getNumeroVinChassis() === $data['numero_vin_chassis'] && isset($data['numero_vin_chassis'])) $doublons["numero_vin_chassis"] = $demande->getNumeroVinChassis();
                return $doublons;
            }
            if($demande && !$demande->getGroupe()){
                $demande->setStatus(null);
                $this->demandeRepository->add($demande);
                if($demande->getPayment()) {
                    $demande->setPayment(null);
                    $paymentRepository->remove($demande->getPayment(), true);
                }
            }
            return $demande;
        }

        if(array_key_exists("numero_carte_grise", $data) && array_key_exists("numero_immatriculation", $data) && array_key_exists("numero_vin_chassis", $data)){
            $demande = $this->demandeRepository->findOneByNumeroCarteGriseOrNumeroImmatriculationOrNumeroVinChassis(
                $data['numero_carte_grise'],
                $data['numero_immatriculation'],
                $data['numero_vin_chassis']
            );
            if($demande && in_array($demande->getStatus(), ['CLOSED', 'PAYE'])){
                if($demande->getNumeroCarteGrise() === $data['numero_carte_grise'] && isset($data['numero_carte_grise'])) $doublons["numero_carte_grise"] = $demande->getNumeroCarteGrise();
                if ($demande->getNumeroImmatriculation() === $data['numero_immatriculation'] && !empty($data['numero_immatriculation'])) $doublons["numero_immatriculation"] = $demande->getNumeroImmatriculation();
                if($demande->getNumeroVinChassis() === $data['numero_vin_chassis'] && isset($data['numero_vin_chassis'])) $doublons["numero_vin_chassis"] = $demande->getNumeroVinChassis();
                return $doublons;
            }
            if($demande && !$demande->getGroupe()){
                $demande->setStatus(null);
                $this->demandeRepository->add($demande);
               if($demande->getPayment()) {
                   $demande->setPayment(null);
                   $paymentRepository->remove($demande->getPayment(), true);
               }
            }
            return $demande;
        }

        return null;
    }


    /**
     * @param Demande|null $demande
     * @param string $viewTemplate
     * @return string|null
     */
    public function generateReceipt(?Demande $demande)
    {
        try {
            $qrCodeData = self::WEBSITE_URL . "/verify/receipt/" . $demande->getReceiptNumber(); // getReceiptNumber();
            $content = $this->pdfGenerator->generateBarCode($qrCodeData, 50, 50);
            $folder = self::MEDIA_DIR . $demande->getReceiptNumber();
            if(!file_exists(self::MEDIA_DIR)) mkdir(self::MEDIA_DIR, 0777, true);
            file_put_contents( $folder . "_barcode.png", $content);

            $viewTemplate = 'frontend/bs/receipt-pdf.html.twig';
            $content = $this->pdfGenerator->generatePdf($viewTemplate, ['demande' => $demande]);
            file_put_contents($folder . "_receipt.pdf", $content);
            if(file_exists($folder . "_barcode.png")) \unlink($folder . "_barcode.png");
            return $content ?? null;


        }catch(\Exception $e){
            if(file_exists($folder . "_barcode.png")) \unlink($folder . "_barcode.png");
            if(file_exists($folder . "_receipt.pdf")) \unlink($folder . "_receipt.pdf");
            return null;
        }
    }

    /**
     * @param Demande|null $demande
     * @param string $viewTemplate
     * @return PdfResponse
     */
    public function downloadPdfReceipt(?Demande $demande){
        set_time_limit(0);
        $content = $this->generateReceipt($demande);
        return new PdfResponse($content, 'recu_macaron.pdf');
    }
}
