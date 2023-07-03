<?php

namespace App\Service\Demande;

use App\Entity\Demande;
use App\Helper\DemandeAssetHelper;
use Knp\Snappy\Pdf;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ReceiptGeneratorService
{
    public function __construct(private ContainerInterface $container, private Pdf  $pdfGenerator){}

    public function generate(?Demande $demande)
    {
        $binDir = $this->container->get('kernel')->getProjectDir();
        $data['twig_view'] = $binDir . "/templates/frontend/pages/receipt-pdf.html.twig";
        $data['demande'] = $demande;
        $this->pdfGenerator->generate($data);
    }
}
