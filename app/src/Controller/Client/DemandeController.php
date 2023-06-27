<?php

namespace App\Controller\Client;

use App\Entity\Demande;
use App\Entity\Payment;
use App\Repository\DemandeRepository;
use App\Service\Demande\DemandeService;
use App\Service\Payment\PaymentService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: 'demande')]
class DemandeController extends AbstractController
{
    #[Route(path: '/selecttype', name: 'demande_select_type', methods: ['POST', 'GET'])]
    public function selectDemandeType(Request $request): Response
    {
        $auth_id = $request->get('authid');
        if(!$auth_id) return $this->redirectToRoute('auth');
        return $this->render('frontend/bs/select-demande-type.html.twig', ["authid" => $auth_id]);
    }

    #[Route(path: '/formulaire', name: 'demande_new', methods: ['POST', 'GET'])]
    public function demande(Request $request, DemandeService $demandeService): Response
    {
        if ($request->getMethod() === "GET") {
            $document = $request->get("document");
            $authid = $request->get("authid");
            if(empty($document && $authid)) return $this->redirectToRoute('demande_select_type');
            return $this->render('frontend/bs/formulaire_demande.html.twig', [
                "document" => $document,
                "authid" => $authid
            ]);
        } elseif ($request->getMethod() === "POST") {
            $data = $request->request->all();
            $demande = $demandeService->create($data);
            return $this->redirectToRoute('demande_recap', ['id' => $demande?->getId()]);
        }

        return $this->redirectToRoute('auth');
    }

    #[Route(path: '/formulaire/edit/{id}', name: 'demande_edit', methods: ['POST', 'GET'])]
    public function formulaireEditDemande(?Demande $demande, Request $request, DemandeService $demandeService): Response
    {
        if ($request->getMethod() === "GET") {
            return $this->render('frontend/pages/demande_edit.html.twig', ['demande' => $demande]);
        } elseif ($request->getMethod() === "POST") {
            $data = $request->request->all();
            if ($demande) {
                $demande = $demandeService->update($demande, $data);
                return $this->redirectToRoute('demande_recap', ['id' => $demande->getId()]);
            }
        }
        return $this->redirectToRoute('home');
    }

    #[Route(path: '/recap/{id}', name: 'demande_recap', methods: ['POST', 'GET'])]
    public function demandeRecap(?Demande $demande): Response
    {
        return $this->render('frontend/bs/recapitulatif.html.twig', ['demande' => $demande]);
    }

    #[Route(path: '/receipt/{id}', name: 'demande_display_receipt', methods: ['POST', 'GET'])]
    public function demandeShowReceipt(?Payment $payment, PaymentService $paymentService): Response
    {
        $paymentService->generateReceipt($payment, 'frontend/bs/receipt-pdf.html.twig');
        return $this->render('frontend/bs/display-receipt.html.twig', [
            'payment' => $payment
        ]);
    }

    #[Route(path: '/payment/{id}', name: 'demande_paiement', methods: ['POST', 'GET'])]
    public function demandePayment(Demande $demande, Request $request, PaymentService $paymentService): Response
    {
        if (!$demande->getPayment()) {
            $data = [
                "montant" => $demande->getMontant(),
                "operateur" => "WAVE",
                "type" => $request->get('type'),
                "demande" => $demande
            ];

            $paymentService->create($data);
        }
        return $this->render('frontend/bs/payment.html.twig', [
            "payment" => $demande->getPayment()
        ]);
    }

    #[Route(path: '/search', name: 'demande_search')]
    public function searchDemande(Request $request, DemandeRepository $demandeRepository): Response
    {
        $term = $request->get('search_receipt_term');
        $criteria = $request->get('search_receipt_criteria');

        if($term && $criteria){
            if($criteria === 'numero_immatriculation') $demande = $demandeRepository->findOneBy(['numero_immatriculation' => $term]);
            if($criteria === 'numero_chassis') $demande = $demandeRepository->findOneBy(['numero_vin_chassis' => $term]);
            if($criteria === 'numero_recu') $demande = $demandeRepository->findOneBy(['reference' => $term]);
            if($demande){
                if($demande->getMacaron()){
                    $warning = "Vous avez déjà reçu votre macaron. Ce reçu est donc inaccessible";
                    return $this->render('frontend/bs/search-demande.html.twig', ["warning" => $warning ]);
                }else{
                    $payment = $demande->getPayment();
                    return $this->redirectToRoute('demande_display_receipt' , ['id' => $payment->getId()]);
                }
            }else{
                $warning = "Cette demande est introuvable!";
                return $this->render('frontend/bs/search-demande.html.twig', ["warning" => $warning ]);
            }
        }
        return $this->render('frontend/bs/search-demande.html.twig');
    }

    #[Route('/receipt-pdf/{id}', name: 'download_receipt_pdf', methods: ['GET'])]
    public function pdfGenerate(Payment $payment, PaymentService $paymentService): Response {
        return $paymentService->downloadPdfReceipt($payment, "frontend/bs/receipt-pdf.html.twig");
    }

}
