<?php

namespace App\Service\Macaron;

use App\Entity\Demande;
use App\Helper\ImageGenerator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\File;

/**
 *
 */
class MacaronGeneratorService
{
    /**
     * @var ImageGenerator
     */
    private ImageGenerator $imageGenerator;

    /**
     * @var ContainerInterface
     */
    private ContainerInterface $container;

    /**
     * @param ContainerInterface $container
     * @param ImageGenerator $imageGenerator
     */
    public function __construct(ContainerInterface $container, ImageGenerator $imageGenerator)
    {
        $this->container = $container;
        $this->imageGenerator = $imageGenerator;
    }

    /**
     * @param Demande|null $demande
     * @return array|null
     */
    public function mapToCardViewModel(?Demande $demande): ?array
    {
        $data['outputdir'] = "/var/www/html/public/members/" . $demande->getMatricule() . "/";
        if(!file_exists($data['outputdir'])) mkdir($data['outputdir'], 0777, true);
        return $data;
    }

    /**
     * @param Demande|null $demande
     * @return string|null
     */
    public function generate(?Demande $demande): ?File
    {
        if(!$demande) return null;
        $cardData = $this->mapToCardViewModel($demande);
        $cardData['qrcodepath'] = $this->imageGenerator->generateBarCode($cardData['qrcodeurl'], $cardData['qrcodepath'], 50, 50);
        $userData['view_data'] = $cardData;
        $userData['twig_view'] = "admin/print/card.html.twig";
        return $this->imageGenerator->generate($userData);
    }


}
