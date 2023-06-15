<?php

namespace App\Service\Macaron;

use App\Entity\Macaron;
use App\Helper\CsvReaderHelper;
use App\Helper\MacaronAssetHelper;
use App\Helper\PasswordHelper;
use App\Repository\MacaronRepository;
use App\Service\Macaron\DemandeGeneratorService;
use App\Service\Macaron\ReceiptGeneratorService;
use DateTime;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class MacaronService
{
    private DemandeGeneratorService $macaronGeneratorService;

    private ReceiptGeneratorService $macaronReceiptGeneratorService;

    private MacaronAssetHelper $macaronAssetHelper;

    private MacaronRepository $macaronRepository;

    private ContainerInterface $container;
    private UserPasswordHasherInterface $userPasswordHasher;

    private CsvReaderHelper $csvReaderHelper;

    public function __construct(
        ContainerInterface          $container,
        DemandeGeneratorService     $macaronGeneratorService,
        ReceiptGeneratorService     $macaronReceiptGeneratorService,
        MacaronAssetHelper          $macaronAssetHelper,
        MacaronRepository           $macaronRepository,
        UserPasswordHasherInterface $userPasswordHasher,
        CsvReaderHelper             $csvReaderHelper)
    {
        $this->macaronGeneratorService = $macaronGeneratorService;
        $this->macaronReceiptGeneratorService = $macaronReceiptGeneratorService;
        $this->macaronAssetHelper = $macaronAssetHelper;
        $this->macaronRepository = $macaronRepository;
        $this->container = $container;
        $this->userPasswordHasher = $userPasswordHasher;
        $this->csvReaderHelper = $csvReaderHelper;
    }


    /**
     * @throws \Exception
     */
    public function createMacaron(Macaron $macaron): void
    {
        date_default_timezone_set("Africa/Abidjan");

        $macaron->setRoles(['ROLE_USER']);

        $date = new DateTime('now');
        $macaron->setSubscriptionDate($date);

        $macaron->setMatricule($matricule);

        $macaron->setPassword($this->userPasswordHasher->hashPassword($macaron, PasswordHelper::generate()));

        $this->saveMacaronImages($macaron);

        $this->macaronRepository->add($macaron, true);

    }

    /**
     * @param Macaron|null $macaronDto
     * @return void
     */
    public function deleteMacaron(?Macaron $macaron): void
    {

    }

    /**
     * @param Macaron|null $macaron
     * @return Macaron|null
     */
    public function generateSingleMacaronCard(?Macaron $macaron): ?Macaron
    {
        date_default_timezone_set("Africa/Abidjan");
        if ($macaron) {
            if(empty($macaron->getPhoto())) return null;
            $cardImage = $this->macaronGeneratorService->generate($macaron);
            $macaron->setCardPhoto(new File($cardImage));
            $macaron->setModifiedAt(new DateTime());
            return $macaron;
        }
        return null;
    }

    /**
     * @return array
     */
    public function generateMultipleMacaronCards(array $matricules = []): array
    {
        date_default_timezone_set("Africa/Abidjan");
        $macaronList = [];
        if(empty($matricules)){
            $macarons = $this->macaronRepository->findAll();
        }else{
            $macarons = $this->macaronRepository->findBy(["matricule" => $matricules]);
        }

        foreach ($macarons as $macaron) {
            $this->generateSingleMacaronCard($macaron);
            $macaronList[] = $macarons;
        }
        return $macaronList;
    }

    /**
     * @param array $macarons
     * @return string|null
     */
    public function archiveMacaronCards(array $macarons): ?string
    {
        date_default_timezone_set("Africa/Abidjan");
        set_time_limit(0);
        $zipArchive = new \ZipArchive();
        $zipFile = $this->container->getParameter('kernel.project_dir') . '/public/macarons/tmp/macarons.zip';;
        if(file_exists($zipFile)) unlink($zipFile);
        if($zipArchive->open($zipFile, \ZipArchive::CREATE) === true)
        {
            /**@var Macaron $macaronDto **/
            foreach($macarons as $macaron)
            {
                $photoRealPath =  $macaron->getPhoto();
                if(is_file($photoRealPath)) {
                    $zipArchive->addFile($photoRealPath->getRealPath(), $macaron->getMatricule() . '_photo.png');
                }

                $cardPhotoRealPath =  $macaron->getCardPhoto();
                if(is_file($cardPhotoRealPath)) {
                    $zipArchive->addFile($cardPhotoRealPath->getRealPath(), $macaron->getMatricule() . '_card.png');
                }

                $barCodePhotoRealPath = $this->container->getParameter('kernel.project_dir') . "/public/macarons/" . $macaronDto->getMatricule() . "/" . $macaronDto->getMatricule() . "_barcode.png";
                if(is_file($barCodePhotoRealPath)) {
                    $zipArchive->addFile($barCodePhotoRealPath, $macaron->getMatricule() . '_barcode.png');
                }
            }
            $zipArchive->close();
            return $zipFile;
        }
        return null;
    }


    public function getMacaronCardsList(array $macarons){
        $zipFile = $this->container->getParameter('kernel.project_dir') . '/public/vehicule/tmp/vehicules.zip';;
         if(!file_exists($zipFile)){
             $this->generateMultipleMacaronCards($macarons);
         }
    }

    /**
     * @param Macaron|null $macaron
     * @return void
     */
    public function generateMacaronPdfReceipt(?Macaron $macaron): void
    {
        $this->macaronReceiptGeneratorService->generate($macaron);
    }

    /**
     * @param Macaron $macaron
     * @return void
     */
    public function storeMacaron(Macaron $macaron): void
    {
         $this->macaronRepository->add($macaron, true);
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
     * @return void
     */
    public function createMacaronFromFile(): void
    {
        set_time_limit(3600);
        $finder = new Finder();
        $uploadDir = $this->container->getParameter('kernel.project_dir') . '/public/uploads/';
        $csvFiles = $finder->in($uploadDir)->name(['*.csv','*.jpg', '*.jpeg','*.png','*.gif']);
        $fs = new Filesystem();
        // remove file after import
        foreach($csvFiles as $file) {
            $rows =  $this->csvReaderHelper->read($file);
            foreach ($rows as $row){
                try{
                    date_default_timezone_set("Africa/Abidjan");
                    $date = new DateTime('now');

                    $macaron = new Macaron();

                    $macaron->setRoles(['ROLE_USER']);
                    if (isset($row["SEXE"])) $macaron->setSex(mb_strtoupper($row["SEXE"], 'UTF-8'));
                    if (isset($row["EMAIL"])) $macaron->setEmail(trim($row["EMAIL"]));
                    if (isset($row["NOM"])) $macaron->setLastName(mb_strtoupper(trim($row["NOM"]), 'UTF-8'));
                    if (isset($row["COMPAGNIE"])) $macaron->setCompany(mb_strtoupper(trim($row["COMPAGNIE"]), 'UTF-8'));
                    if (isset($row["NATIONALITE"])) $macaron->setLastName(mb_strtoupper(trim($row["NATIONALITE"]), 'UTF-8'));
                    if (isset($row["PRENOMS"])) $macaron->setFirstName(mb_strtoupper(trim($row["PRENOMS"]), 'UTF-8'));
                    if (isset($row["DATE_NAISSANCE"])) $macaron->setDateOfBirth(new DateTime($row["DATE_NAISSANCE"]));
                    if (isset($row["LIEU_NAISSANCE"])) $macaron->setBirthCity(mb_strtoupper(trim($row["LIEU_NAISSANCE"])));
                    if (isset($row["NUMERO_PERMIS"])) $macaron->setDrivingLicenseNumber($row["NUMERO_PERMIS"]);
                    if (isset($row["NUMERO_PIECE"])) $macaron->setIdNumber($row["NUMERO_PIECE"]);
                    if (isset($row["TYPE_PIECE"])) $macaron->setIdType(mb_strtoupper(trim($row["TYPE_PIECE"])));
                    if (isset($row["PAYS"])) $macaron->setCountry(mb_strtoupper(trim($row["PAYS"])));
                    if (isset($row["VILLE"])) $macaron->setCity(mb_strtoupper($row["VILLE"], 'UTF-8'));
                    if (isset($row["COMMUNE"])) $macaron->setCommune(mb_strtoupper($row["COMMUNE"], 'UTF-8'));
                    if (isset($row["MOBILE"])) $macaron->setMobile($row["MOBILE"]);
                    if (isset($row["FIXE"])) $macaron->setPhone($row["FIXE"]);
                    if (isset($row["TITRE"])) $macaron->setTitre(mb_strtoupper(trim($row["TITRE"])));

                    $macaron->setPassword($this->userPasswordHasher->hashPassword($macaron, PasswordHelper::generate()));

                    if (array_key_exists("DATE_SOUSCRIPTION", $row)) {
                        if (empty($row["DATE_SOUSCRIPTION"])) $macaron->setSubscriptionDate($date);
                        else $macaron->setSubscriptionDate(new DateTime($row["DATE_SOUSCRIPTION"]));
                    }

                    if (array_key_exists("DATE_EXPIRATION_SOUSCRIPTION", $row)) {
                        $expiredDate = new DateTime($row["DATE_SOUSCRIPTION"]);
                        //   $expiredDate = $expiredDate->add(new \DateInterval("P1Y"));
                        $expiredDate = $expiredDate->format('Y-12-31');
                        if (!empty($row["DATE_EXPIRATION_SOUSCRIPTION"])) $macaron->setSubscriptionExpireDate(new DateTime($row["DATE_EXPIRATION_SOUSCRIPTION"]));
                        else $macaron->setSubscriptionExpireDate(new DateTime($expiredDate));
                    }

                    $this->storeAsset($row["PHOTO"], $uploadDir, $macaron);
                    $this->storeAsset($row["PHOTO_PIECE_RECTO"], $uploadDir, $macaron);
                    $this->storeAsset($row["PHOTO_PIECE_VERSO"], $uploadDir, $macaron);
                    $this->storeAsset($row["PHOTO_PERMIS_RECTO"], $uploadDir, $macaron);
                    $this->storeAsset($row["PHOTO_PERMIS_VERSO"], $uploadDir, $macaron);
                    $this->macaronRepository->add($macaron, true);

                }
                catch(\Exception $e){
                    continue;
                }
            }
        }
        $fs->remove($csvFiles);
    }

    /**
     * @param Macaron $macaron
     * @return void
     */

    public function saveMacaronImages(Macaron $macaron): Macaron
    {
        if ($macaron->getPhoto()) {
            $fileName = $this->macaronAssetHelper->uploadAsset(
                $macaron->getPhoto(),
                $macaron->getMatricule()
            );
            if ($fileName) $macaron->setPhoto($fileName);
        }

        return $macaron;
    }


    /**
     * @param $row
     * @param string $uploadDir
     * @param Macaron $macaron
     * @return void
     */
    public function storeAsset($row, string $uploadDir, Macaron $macaron): void
    {
        if (isset($row) && !empty($row)) {
            $photo = new File($uploadDir . $row, false);
            if (file_exists($photo->getPathname())) {
                $fileName = $this->macaronAssetHelper->uploadAsset($photo, $macaron->getMatricule());
                if ($fileName) $macaron->setPhoto($fileName);
            }
        }
    }

}
