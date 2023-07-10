<?php

namespace App\Service\Macaron;

use App\Entity\Macaron;
use App\Helper\MacaronAssetHelper;
use App\Repository\MacaronRepository;
use App\Service\Demande\DemandeGeneratorService;
use Symfony\Component\DependencyInjection\ContainerInterface;

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

    public function store(Macaron $macaron){
        $this->macaronRepository->add($macaron);
    }
}
