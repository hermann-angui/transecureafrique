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
    public function __construct(private ContainerInterface $container, private MacaronRepository $macaronRepository)
    {
    }

    public function createMacaron(array $data): void
    {
        date_default_timezone_set("Africa/Abidjan");
        $macaron = new Macaron();
        $this->macaronRepository->add($macaron, true);
    }

    public function getMacaronEnCirulation(): ?int
    {
        return $this->macaronRepository->count([]);
    }
}
