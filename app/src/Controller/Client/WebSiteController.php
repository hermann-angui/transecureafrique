<?php

namespace App\Controller\Client;

use App\Entity\Demande;
use App\Entity\OtpCode;
use App\Entity\User;
use App\Form\UserFormType;
use App\Helper\ImageGenerator;
use App\Repository\DemandeRepository;
use App\Repository\OtpCodeRepository;
use App\Service\Demande\DemandeService;
use App\Service\InfoBip\InfoBipService;
use App\Service\Otp\OtpService;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Knp\Snappy\Pdf;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class WebSiteController extends AbstractController
{
    private const WEBSITE_URL = "http://sfp-macaron.develop/check/";

    private const MEDIA_DIR = "/var/www/html/public/media/" ;

    #[Route(path: '/', name: 'home')]
    public function home(Request $request): Response
    {
        return $this->render('frontend/pages/index.html.twig');
    }

    #[Route(path: '/formulaire', name: 'formulaire', methods: ['POST', 'GET'])]
    public function demande(Request $request, DemandeRepository $demandeRepository, OtpCodeRepository $otpCodeRepository): Response
    {
        if($request->getMethod() === "GET") {
            $document = $request->get("document");
            $authid   = $request->get("authid");
            return $this->render('frontend/pages/formulaire_demande.html.twig', [
                "document" => $document,
                "authid" => $authid
            ]);
        }
        elseif($request->getMethod()==="POST"){

            $data = $request->request->all();
            $demande = new Demande();
            $demande->setReference(strtoupper(uniqid()));
            if(array_key_exists("numero_carte_grise", $data)) $demande->setNumeroCarteGrise($data["numero_carte_grise"]);
            if(array_key_exists("numero_recepisse", $data)) $demande->setNumeroRecepisse($data["numero_recepisse"]);
            $demande->setMontant(10100);
            $demande->setNumeroImmatriculation($data["numero_immatriculation"]);
            $demande->setDateDePremiereMiseEnCirulation(new \DateTime($data["date_de_premiere_mise_en_cirulation"]));
            $demande->setDateDEdition(new \DateTime($data["date_d_edition"]));
            $demande->setIdentiteProprietaire($data["identite_proprietaire"]);
            $demande->setIdentiteProprietairePiece($data["identite_proprietaire_piece"]);
            $demande->setMarqueDuVehicule($data["marque_du_vehicule"]);
            $demande->setGenreVehicule($data["genre_vehicule"]);
            $demande->setTypeCommercial($data["type_commercial"]);
            $demande->setCouleurVehicule($data["couleur_vehicule"]);
            $demande->setCarroserieVehicule($data["carroserie_vehicule"]);
            $demande->setEnergieVehicule($data["energie_vehicule"]);
            $demande->setPlacesAssises($data["places_assises"]);
            $demande->setUsageVehicule($data["usage_vehicule"]);
            $demande->setPuissanceFiscale($data["puissance_fiscale"]);
            $demande->setNombreDEssieux($data["nombre_d_essieux"]);
            $demande->setCylindree($data["cylindree"]) ;
            $demande->setNumeroVinChassis($data["numero_vin_chassis"]);
            $demande->setSocieteDeCredit($data[ "societe_de_credit"]);
            $demande->setTypeTechnique($data["type_technique"]);
            $demande->setNumeroDImmatriculationPrecedent($data["numero_d_immatriculation_precedent"]);

            $demande->setDateRendezVous(new \DateTime("tomorrow"));

            if(array_key_exists("authid", $data)) {
                $otpCode = $otpCodeRepository->find($data["authid"]);
                if($otpCode) {
                    $demande->setOtpcode($otpCode);
                }
            }

            $demandeRepository->add($demande, true);
            return $this->redirectToRoute('demande_recap', ['id' => $demande->getId()]);
        }

        return $this->redirectToRoute('auth');
    }

    #[Route(path: '/demande', name: 'selectdemandetype', methods: ['POST', 'GET'])]
    public function selectDemandeType(Request $request): Response
    {
        $auth_id = $request->get('authid');
        return $this->render('frontend/pages/select-demande-type.html.twig', ["authid" => $auth_id ]);
    }

    #[Route(path: '/formulaire/demande/edit/{id}', name: 'formulaire_edit_demande', methods: ['POST', 'GET'])]
    public function formulaireEditDemande(Request $request,
                                          DemandeRepository $demandeRepository,
                                          ImageGenerator $imageGenerator): Response
    {
        if($request->getMethod() === "GET"){
            $demande = $demandeRepository->find($request->get("id"));
            return $this->render('frontend/pages/formulaire_edit_demande.html.twig', ['demande' => $demande]);
        }
        elseif($request->getMethod()==="POST"){
            $data = $request->request->all();
            $demande = $demandeRepository->find($request->get("id"));
            if($demande) {
                if(array_key_exists("numero_carte_grise", $data)) $demande->setNumeroCarteGrise($data["numero_carte_grise"]);
                if(array_key_exists("numero_recepisse", $data)) $demande->setNumeroRecepisse($data["numero_recepisse"]);
                $demande->setNumeroImmatriculation($data["numero_immatriculation"]);
                $demande->setMacaronQrcodeNumber($data["macaron_qrcode_number"]);
                $demande->setDateDePremiereMiseEnCirulation(new \DateTime($data["date_de_premiere_mise_en_cirulation"]));
                $demande->setDateDEdition(new \DateTime($data["date_d_edition"]));
                $demande->setIdentiteProprietaire($data["identite_proprietaire"]);
                $demande->setIdentiteProprietairePiece($data["identite_proprietaire_piece"]);
                $demande->setMarqueDuVehicule($data["marque_du_vehicule"]);
                $demande->setGenreVehicule($data["genre_vehicule"]);
                $demande->setTypeCommercial($data["type_commercial"]);
                $demande->setCouleurVehicule($data["couleur_vehicule"]);
                $demande->setCarroserieVehicule($data["carroserie_vehicule"]);
                $demande->setEnergieVehicule($data["energie_vehicule"]);
                $demande->setPlacesAssises($data["places_assises"]);
                $demande->setUsageVehicule($data["usage_vehicule"]);
                $demande->setPuissanceFiscale($data["puissance_fiscale"]);
                $demande->setNombreDEssieux($data["nombre_d_essieux"]);
                $demande->setCylindree($data["cylindree"]) ;
                $demande->setNumeroVinChassis($data["numero_vin_chassis"]);
                $demande->setSocieteDeCredit($data[ "societe_de_credit"]);
                $demande->setTypeTechnique($data["type_technique"]);
                $demande->setNumeroDImmatriculationPrecedent($data["numero_d_immatriculation_precedent"]);

                $qrCodeData = self::WEBSITE_URL . $data["macaron_qrcode_number"];
                $imageGenerator->generateBarCode($qrCodeData, self::MEDIA_DIR. $data["macaron_qrcode_number"] . "_barcode.png", 50, 50);

                $demande->setMacaronQrcodeImage($data["macaron_qrcode_number"] . "_barcode.png");
                $demandeRepository->add($demande, true);
                return $this->redirectToRoute('demande_recap', ['id' => $demande->getId()]);

            }
        }

        return $this->redirectToRoute('home');
    }

    #[Route(path: '/demande/edit/{id}', name: 'demande_edit', methods: ['POST', 'GET'])]
    public function demandeEdit(Demande $demande): Response
    {
        return $this->render('frontend/pages/demande-edit.html.twig', ['demande' => $demande]);
    }

    #[Route(path: '/demande/recap/{id}', name: 'demande_recap', methods: ['POST', 'GET'])]
    public function demandeRecap(Demande $demande): Response
    {
        return $this->render('frontend/pages/recapitulatif.html.twig', ['demande' => $demande]);
    }

    #[Route(path: '/demande/receipt/{id}', name: 'demande_display_receipt', methods: ['POST', 'GET'])]
    public function demandeShowReceipt(Demande $demande,
                                   DemandeRepository $demandeRepository,
                                   ImageGenerator $imageGenerator,
                                   Pdf $knpSnappyPdf): Response
    {
        $qrCodeData = "http://sfp-macaron.develop/carte-check/" . $demande->getNumeroVinChassis();
        $imageGenerator->generateBarCode($qrCodeData, self::MEDIA_DIR. $demande->getNumeroVinChassis() . "_barcode.png", 50, 50);

        $demande->setQrcode($demande->getNumeroVinChassis() . "_barcode.png");

        $html = $this->renderView('frontend/pages/receipt-pdf.html.twig', array(
            'demande'  => $demande
        ));
        $content = $knpSnappyPdf->getOutputFromHtml($html);
        file_put_contents(self::MEDIA_DIR. $demande->getNumeroVinChassis() . "_receipt.pdf", $content);

        $demandeRepository->add($demande, true);
        return $this->render('frontend/pages/display-receipt.html.twig', ['demande' => $demande]);
    }

    #[Route(path: '/carte-grise/{numero_carte_grise}', name: 'carte_grise_show', methods: ['POST', 'GET'])]
    public function carteGriseShow(Request $request, DemandeRepository $demandeRepository): Response
    {
      //  $demande = $demandeRepository->findOneBy(['numero_vin_chassis'=> $request->get('numero_vin_chassis')]);
      //  return $this->render('frontend/pages/carte-grise-show.html.twig', ['demande' => $demande]);
        return $this->render('frontend/pages/carte-grise-show.html.twig');
    }

    #[Route(path: '/demande/payment/{id}', name: 'demande_paiement', methods: ['POST', 'GET'])]
    public function demandePaiement(Demande $demande, Request $request, DemandeRepository $demandeRepository): Response
    {
        $paiementType = $request->get('type');
        switch($paiementType){
            case 'mtn_money';
                $demande->setPaiementType("MTN MOBILE MONEY");
                break;
            case 'orange_money';
                $demande->setPaiementType("ORANGE MOBILE MONEY");
                break;
            case 'wave_money';
                $demande->setPaiementType("ORANGE MOBILE MONEY");
                break;
            default:
                $demande->setPaiementType(null);
        }

        $demandeRepository->add($demande, true);
        return $this->render('frontend/pages/payment.html.twig', ['demande' => $demande]);
    }

    #[Route(path: '/auth', name: 'auth')]
    public function login(Request $request): Response
    {
        return $this->render('frontend/pages/auth.html.twig');
    }

    #[Route(path: '/check_otp', name: 'check_otp', methods: ['GET', 'POST'])]
    public function checkOtp(Request $request, OtpCodeRepository $otpCodeRepository): Response
    {
        if($request->getMethod() === "GET" ) return $this->redirectToRoute("auth");
        $code = $request->request->get('otpcode');
        $otpCode = $otpCodeRepository->findOneBy(['code' =>  $code]);
        if($otpCode){
            return $this->redirectToRoute('selectdemandetype', ['authid'=> $otpCode->getId() ]);
        }else{
            return $this->render('frontend/pages/otp.html.twig', ["error" => "Le Code $code est  incorrect. Veuillez entrer le code reçu par SMS "]);
        }
    }

    #[Route(path: '/otp', name: 'otp')]
    public function otp(Request $request, InfoBipService $infoBipService, OtpCodeRepository $otpCodeRepository ): Response
    {
        if(!$request->get('numerotelInput')) return $this->redirectToRoute('auth');
        // Si la personne avait deja recu le code d'authentification
        // lui ramener le code existant sans lui envoyé un nouveau code via sms
        // ceci necessiterait des couts en cas de repetition excessives d'envoi
        do {
            $done = true;
            $generatedCode = OtpService::generate(6);
            $entry = $otpCodeRepository->findOneBy(['code' => $generatedCode]);
            if($entry) $done = false;
        }while(!$done);

        $message = "Votre code de vérification transecure.ci : " . $generatedCode;
        $result = $infoBipService->sendMessageTo($message, $request->get('numerotelInput'));

        if(!in_array($result["status"], ["REJECTED","FAILED","ERROR"])){
            $otpCode = new OtpCode();
            $otpCode->setCode($generatedCode);
            $otpCode->setWebserviceReference($result["messageId"]);
            $otpCode->setPhone($request->get('numerotelInput'));
            $otpCode->setIsExpired(false);
            $otpCode->setCreatedAt(new \DateTime('now'));
            $otpCode->setModifiedAt(new \DateTime('now'));
            $otpCodeRepository->add($otpCode, true);
        }

        return $this->render('frontend/pages/otp.html.twig');
    }

    #[Route(path: '/search-demande', name: 'search_demande')]
    public function searchDemande(Request $request, DemandeRepository $demandeRepository, ImageGenerator $imageGenerator): Response
    {
        if($request->get('numero_recu')){
            $demande = $demandeRepository->findOneBy(['reference' => $request->get('numero_recu')]);
            if($demande && $demande->getMacaronQrcodeNumber() ){
                $data['twig_view'] = "frontend/pages/macaron.html.twig";
                $data['view_data']["qrcode"] = self::MEDIA_DIR. $demande->getMacaronQrcodeImage();
                $data['view_data']["cardpath"] = self::MEDIA_DIR. $demande->getMacaronQrcodeNumber() . "_macaron.png";
                $file = $imageGenerator->generate($data);
                $macaron = $demande->getMacaronQrcodeNumber() . "_macaron.png";
                return $this->render('frontend/pages/search-demande.html.twig', ['macaron' => $macaron]);
            }else{
                $message = "Votre macaron n'est pas encore près";
                return $this->render('frontend/pages/search-demande.html.twig', ['message' => $message]);
            }
        }
        return $this->render('frontend/pages/search-demande.html.twig');
    }

    #[Route(path: '/check/{macaron_qrcode_number}', name: 'macaron_check')]
    public function macaronCheck(Request $request, DemandeRepository $demandeRepository): Response
    {
        $demande = $demandeRepository->findOneBy(["macaron_qrcode_number" => $request->get("macaron_qrcode_number")]);
        return $this->render('frontend/pages/macaron_check.html.twig', ["demande" => $demande]);
    }

    #[Route('/receipt-pdf/{id}', name: 'download_receipt_pdf', methods: ['GET'])]
    public function pdfGenerate(Demande $demande, Pdf $knpSnappyPdf): Response
    {
        $html = $this->renderView('frontend/pages/public_profile.html.twig', array(
            'demande'  => $demande
        ));

        return new PdfResponse(
            $knpSnappyPdf->getOutputFromHtml($html),
            'file.pdf'
        );
    }
}
