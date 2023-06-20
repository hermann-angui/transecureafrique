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
use Symfony\Component\Validator\Constraints\Date;

class MacaronController extends AbstractController
{

    #[Route(path: '/macaron', name: 'macaron', methods: ['POST', 'GET'])]
    public function macaron(Request $request, DemandeRepository $demandeRepository, OtpCodeRepository $otpCodeRepository): Response
    {
        if($request->getMethod()==="GET") {
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
                if(!$otpCode) {
                    $otpCode->setDemande($otpCode);
                    $otpCodeRepository->add($otpCode, true);
                }
            }

            $demandeRepository->add($demande, true);
            return $this->redirectToRoute('demande_recap', ['id' => $demande->getId()]);
        }

        return $this->redirectToRoute('auth');
    }

}
