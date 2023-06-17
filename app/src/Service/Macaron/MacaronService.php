<?php

namespace App\Service\Macaron;

use App\Entity\Macaron;
use App\Helper\CsvReaderHelper;
use App\Helper\MacaronAssetHelper;
use App\Repository\MacaronRepository;
use App\Service\Demande\DemandeGeneratorService;
use App\Service\Demande\ReceiptGeneratorService;
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
