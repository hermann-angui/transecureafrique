<?php

namespace App\Controller\Client;

use App\Entity\Macaron;
use App\Entity\Payment;
use App\Repository\MacaronRepository;
use App\Repository\PaymentRepository;
use App\Service\Wave\WaveService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


#[Route('/payment')]
class PaymentController extends AbstractController
{
    #[Route(path: '/do/{id}', name: 'do_payment')]
    public function doPayment(Payment $payment,
                              WaveService $waveService,
                              PaymentRepository $paymentRepository): Response
    {
        $response = $waveService->makePayment($payment);
        if($response) {
            $payment->setStatus($response->getPaymentStatus());
            $payment->setReference($response->getClientReference());
            $payment->setMontant($response->getAmount());
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
                    $payment->setStatus($data["payment_status"]);
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
                                                      MacaronRepository $macaronRepository,
                                                      PaymentRepository $paymentRepository): Response
    {
        $payment = $paymentRepository->findOneBy(["reference" => $request->get("id")]);
        if ($payment) {
            $macaron  = new Macaron();
            $macaron->setDemande($payment->getDemande());
            $macaron->setStatus("WAITING_FOR_REVIEW");
            $macaron->setCreatedAt(new \DateTime('now'));
            $macaronRepository->add($macaron, true);
            $payment->setModifiedAt(new \DateTime());
            $paymentRepository->add($payment, true);
        }
       return $this->redirectToRoute('demande_display_receipt', ["id" => 1, "status" => $status]);
    }

    private function updatePaymentStatus(array $data,): void
    {

    }

}
