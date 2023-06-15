<?php

namespace App\Service\Macaron;

use App\Entity\Macaron;
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
    public function __construct(ContainerInterface $container,
                                ImageGenerator $imageGenerator)
    {
        $this->container = $container;
        $this->imageGenerator = $imageGenerator;
    }

    /**
     * @param Macaron|null $automobiliste
     * @return array|null
     */
    public function mapToCardViewModel(?Macaron $macaron): ?array
    {
        $data['fullname'] = $macaron->getLastName() . " " . $macaron->getFirstName();
        $data['titre'] = $macaron->getTitre();
        $data['matricule'] = $macaron->getMatricule();
        $data['outputdir'] = "/var/www/html/public/members/" . $macaron->getMatricule() . "/";
        if(!file_exists($data['outputdir'])) mkdir($data['outputdir'], 0777, true);
        $data['cardbg'] = "/var/www/html/public/assets/files/card_member_front.jpg";
        $data['photopath'] =  $macaron->getPhoto();
        $data['qrcodepath'] = $data['outputdir'] . $macaron->getMatricule() . '_barcode.png' ;
        $data['cardpath'] = $data['outputdir'] . $macaron->getMatricule() . '_card.png' ;
        $data['qrcodeurl'] = $this->container->getParameter('profile_url')  . "/" . $macaron->getMatricule();
        $data['expiredate'] = "Expire le " . $macaron->getSubscriptionExpireDate()->format('d/m/Y');
        $data['website'] = "www.synacvtcci.org";

        return $data;
    }

    /**
     * @param Macaron|null $macaron
     * @return string|null
     */
    public function generate(?Macaron $macaron): ?File
    {
        if(!$macaron) return null;
        $cardData = $this->mapToCardViewModel($macaron);
        $cardData['qrcodepath'] = $this->imageGenerator->generateBarCode($cardData['qrcodeurl'], $cardData['qrcodepath'], 50, 50);
        $userData['view_data'] = $cardData;
        $userData['twig_view'] = "admin/print/card.html.twig";
        return $this->imageGenerator->generate($userData);
    }


}
