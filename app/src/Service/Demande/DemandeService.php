<?php

namespace App\Service\Demande;

use App\Entity\Demande;
use App\Entity\Subscription;
use App\Helper\CsvReaderHelper;
use App\Helper\DemandeAssetHelper;
use App\Helper\PasswordHelper;
use App\Repository\DemandeRepository;
use App\Service\Wave\WaveCheckoutRequest;
use DateTime;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Uuid;

class DemandeService
{
    private DemandeGeneratorService $demandeGeneratorService;

    private ReceiptGeneratorService $demandeReceiptGeneratorService;

    private DemandeAssetHelper $demandeAssetHelper;

    private DemandeRepository $demandeRepository;

    private ContainerInterface $container;
    private UserPasswordHasherInterface $userPasswordHasher;

    private CsvReaderHelper $csvReaderHelper;

    public function __construct(
        ContainerInterface          $container,
        DemandeGeneratorService     $demandeGeneratorService,
        ReceiptGeneratorService     $demandeReceiptGeneratorService,
        DemandeAssetHelper          $demandeAssetHelper,
        DemandeRepository           $demandeRepository,
        UserPasswordHasherInterface $userPasswordHasher,
        CsvReaderHelper             $csvReaderHelper)
    {
        $this->demandeGeneratorService = $demandeGeneratorService;
        $this->demandeReceiptGeneratorService = $demandeReceiptGeneratorService;
        $this->demandeAssetHelper = $demandeAssetHelper;
        $this->demandeRepository = $demandeRepository;
        $this->container = $container;
        $this->userPasswordHasher = $userPasswordHasher;
        $this->csvReaderHelper = $csvReaderHelper;
    }

    /**
     * @param Demande|null $demande
     * @return void
     */
    public function deleteDemande(?Demande $demande): void
    {

    }

    /**
     * @param Demande|null $demande
     * @return Demande|null
     */
    public function generateSingleDemandeCard(?Demande $demande): ?Demande
    {
        date_default_timezone_set("Africa/Abidjan");
        if ($demande) {
            if(empty($demande->getPhoto())) return null;
            $cardImage = $this->demandeGeneratorService->generate($demande);
            $demande->setCardPhoto(new File($cardImage));
            $demande->setModifiedAt(new DateTime());
            return $demande;
        }
        return null;
    }

    /**
     * @return array
     */
    public function generateMultipleDemandeCards(array $matricules = []): array
    {
        date_default_timezone_set("Africa/Abidjan");
        $demandeList = [];
        if(empty($matricules)){
            $demandes = $this->demandeRepository->findAll();
        }else{
            $demandes = $this->demandeRepository->findBy(["matricule" => $matricules]);
        }

        foreach ($demandes as $demande) {
            $this->generateSingleDemandeCard($demande);
            $demandeList[] = $demandes;
        }
        return $demandeList;
    }

    /**
     * @param array $demandes
     * @return string|null
     */
    public function archiveDemandeCards(array $demandes): ?string
    {
        date_default_timezone_set("Africa/Abidjan");
        set_time_limit(0);
        $zipArchive = new \ZipArchive();
        $zipFile = $this->container->getParameter('kernel.project_dir') . '/public/demandes/tmp/demandes.zip';;
        if(file_exists($zipFile)) unlink($zipFile);
        if($zipArchive->open($zipFile, \ZipArchive::CREATE) === true)
        {
            /**@var Demande $demandeDto **/
            foreach($demandes as $demande)
            {
                $photoRealPath =  $demande->getPhoto();
                if(is_file($photoRealPath)) {
                    $zipArchive->addFile($photoRealPath->getRealPath(), $demande->getMatricule() . '_photo.png');
                }

                $cardPhotoRealPath =  $demande->getCardPhoto();
                if(is_file($cardPhotoRealPath)) {
                    $zipArchive->addFile($cardPhotoRealPath->getRealPath(), $demande->getMatricule() . '_card.png');
                }

                $barCodePhotoRealPath = $this->container->getParameter('kernel.project_dir') . "/public/demandes/" . $demandeDto->getMatricule() . "/" . $demandeDto->getMatricule() . "_barcode.png";
                if(is_file($barCodePhotoRealPath)) {
                    $zipArchive->addFile($barCodePhotoRealPath, $demande->getMatricule() . '_barcode.png');
                }
            }
            $zipArchive->close();
            return $zipFile;
        }
        return null;
    }


    public function getDemandeCardsList(array $demandes){
        $zipFile = $this->container->getParameter('kernel.project_dir') . '/public/vehicule/tmp/vehicules.zip';;
         if(!file_exists($zipFile)){
             $this->generateMultipleDemandeCards($demandes);
         }
    }

    /**
     * @param Demande|null $demande
     * @return void
     */
    public function generateDemandePdfReceipt(?Demande $demande): void
    {
        $this->demandeReceiptGeneratorService->generate($demande);
    }

    /**
     * @param Demande $demande
     * @return void
     */
    public function storeDemande(Demande $demande): void
    {
         $this->demandeRepository->add($demande, true);
    }

    /**
     * @return string
     */
    public function generateSampleCsvFile()
    {
        date_default_timezone_set("Africa/Abidjan");
        $sampleRealPath = $this->container->getParameter('kernel.project_dir') . "/public/assets/files/sample.csv";
        $columns = [
            "TITRE",
            "MATRICULE",
            "NOM",
            "PRENOMS",
            "PHOTO",
            "SEXE",
            "EMAIL",
            "WHATSAPP",
            "COMPAGNIE",
            "DATE_NAISSANCE",
            "LIEU_NAISSANCE",
            "NUMERO_PERMIS",
            "NUMERO_PIECE",
            "TYPE_PIECE",
            "PAYS",
            "VILLE",
            "COMMUNE",
            "MOBILE",
            "FIXE",
            "QUARTIER",
            "DATE_SOUSCRIPTION",
            "DATE_EXPIRATION_SOUSCRIPTION",
            "PHOTO_PIECE_RECTO",
            "PHOTO_PIECE_VERSO",
            "PHOTO_PERMIS_RECTO",
            "PHOTO_PERMIS_VERSO"
        ];
        $fp = fopen($sampleRealPath, "w+");
        fputcsv($fp, $columns);
        fputcsv($fp, []);
        fclose($fp);
        return $sampleRealPath;
    }

    /**
     * @param $row
     * @param string $uploadDir
     * @param Demande $demande
     * @return void
     */
    public function storeAsset($row, string $uploadDir, Demande $demande): void
    {
        if (isset($row) && !empty($row)) {
            $photo = new File($uploadDir . $row, false);
            if (file_exists($photo->getPathname())) {
                $fileName = $this->demandeAssetHelper->uploadAsset($photo, $demande->getMatricule());
                if ($fileName) $demande->setPhoto($fileName);
            }
        }
    }

    /**
     * @param string $amount
     * @param UserInterface|null $user
     * @return string|void
     */
    public function payForDemande(string $amount, Demande $demande) : ?string
    {
        try{
            $waveCheckoutRequest = new WaveCheckoutRequest();
            $waveCheckoutRequest->setCurrency("XOF")
                ->setAmount($amount)
                ->setClientReference(Uuid::v4()->toRfc4122())
                ->setSuccessUrl($this->getParameter("wave_success_url"));

            $waveResponse = $this->waveService->checkOutRequest($waveCheckoutRequest);

            if ($waveResponse) {
                $subscription = new Subscription();

                $now = new \DateTime();
                $endDate = $now->add(new \DateInterval('P1Y'));
                $subscription->setAmount($waveResponse->getAmount())
                    ->setCurrency($waveResponse->getCurrency())
                    ->setPaymentReference($waveResponse->getClientReference())
                    ->setCheckoutSessionId($waveResponse->getCheckoutSessionId())
                    ->setSubscriber($user)
                    ->setOperator("WAVE")
                    ->setPaymentMode("WEBSITE")
                    ->setPaymentType("MOBILE_MONEY")
                    ->setPaymentDate($waveResponse->getWhenCreated())
                    ->setSubscriptionStartDate($now)
                    ->setSubscriptionExpireDate($endDate)
                    ->setCreatedAt(new \DateTime())
                    ->setModifiedAt(new \DateTime())
                    ->setPaymentStatus(strtoupper($waveResponse->getPaymentStatus()));

                $this->subscriptionRepository->add($subscription, true);


                return $waveResponse->getWaveLaunchUrl();
            }
        }catch(\Exception $e){
            return null;
        }
    }


}
