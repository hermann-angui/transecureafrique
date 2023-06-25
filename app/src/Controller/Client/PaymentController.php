<?php

namespace App\Controller\Client;

use App\Entity\Payment;
use App\Repository\DemandeRepository;
use App\Repository\PaymentRepository;
use App\Service\Wave\WaveService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Uuid;


#[Route('/payment')]
class PaymentController extends AbstractController
{
    #[Route(path: '/do/{id}', name: 'do_payment')]
    public function doPayment(Payment $payment,
                              WaveService $waveService,
                              DemandeRepository $demandeRepository,
                              PaymentRepository $paymentRepository): Response
    {
        $response = $waveService->makePayment($payment);
        if($response) {
            $payment->setStatus($response->getPaymentStatus());
            $payment->setReference($response->getClientReference());
            $payment->setReceiptNumber($this->generateReference());
            $payment->setMontant($response->getAmount());
            $payment->setType("MOBILE_MONEY");

            $demande = $payment->getDemande();
            //$demande->setStatus("PAYED");
            // $demandeRepository->add($demande, true);

            $paymentRepository->add($payment, true);

            return $this->redirect($response->getWaveLaunchUrl());
        }
        else return $this->redirectToRoute('home');
    }

    #[Route(path: '/wave', name: 'wave_payment_checkout_webhook')]
    public function callbackWavePayment(Request $request,  PaymentRepository $paymentRepository): Response
    {
        $payload =  json_decode($request->getContent(), true);
        if(!empty($payload) && array_key_exists("data", $payload)) {
            $data =  $payload['data'];
            if (!empty($data) && array_key_exists("client_reference", $data)) {
                $payment = $paymentRepository->findOneBy(["reference" => $data["client_reference"]]);
                if ($payment) {
                    $payment->setStatus(strtoupper($data["payment_status"]));
                    $payment->setMontant($data["amount"]);
                    $payment->setCodePaymentOperateur($data["transaction_id"]);
                    $payment->setModifiedAt(new \DateTime());
                    $paymentRepository->add($payment, true);
                }
            }
        }
        return $this->json($payload);
    }

    #[Route(path: '/wave/checkout/{status}', name: 'wave_payment_callback')]
    public function wavePaymentCheckoutStatusCallback($status, Request $request,
                                                      PaymentRepository $paymentRepository): Response
    {
        $payment = $paymentRepository->findOneBy(["reference" => $request->get("ref")]);
        if ($payment && $payment->getStatus()==="SUCCEEDED") {
            return $this->redirectToRoute('demande_display_receipt', [
                "id" => $payment->getId(),
                "status" => $status]
            );
        }else{
            return $this->redirectToRoute('home');
        }
    }

    public function generateReference() {
        $now = new \DateTime();
        $year = $now->format("Y");
        return $year . strtoupper(substr(Uuid::v4()->toRfc4122(), 0, 6));
    }


}
