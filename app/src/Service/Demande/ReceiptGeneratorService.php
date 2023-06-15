<?php

namespace App\Service\Demande;

use App\Entity\Demande;
use App\Helper\DemandeAssetHelper;
use Knp\Snappy\Pdf;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ReceiptGeneratorService
{
    /**
     * @param DemandeAssetHelper ;
     */
    protected DemandeAssetHelper $demandeAssetHelper;

    /**
     * @var Pdf
     */
    protected Pdf $pdfGenerator;

    public function __construct(ContainerInterface $container,
                                DemandeAssetHelper $demandeAssetHelper,
                                Pdf                $pdfGenerator)
    {
        $this->container = $container;
        $this->demandeAssetHelper = $demandeAssetHelper;
        $this->pdfGenerator = $pdfGenerator;
    }

    public function generate(?Demande $demande)
    {
        $binDir = $this->container->get('kernel')->getProjectDir();
        $data['twig_view'] = $binDir . "/templates/frontend/pages/receipt-pdf.html.twig";
        $data['demande'] = $demande;
        $this->pdfGenerator->generate($data);
    }


}
