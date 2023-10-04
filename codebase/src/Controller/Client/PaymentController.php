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


#[Route('/payment')]
class PaymentController extends AbstractController
{
    #[Route(path: '/do/{id}', name: 'do_payment')]
    public function doPayment(Request $request, Payment $payment,
                              WaveService $waveService,
                              PaymentRepository $paymentRepository): Response
    {
        $status = strtoupper($payment->getStatus());

        if(in_array($status, ["SUCCEEDED","PROCESSING"])) {
            $status = "success";
            return $this->redirectToRoute('demande_display_receipt', [
                    "id" => $payment->getId(),
                    "status" => $status]
            );
        }
        $response = $waveService->makePayment($payment);
        if($response) {
            $payment->setStatus($response->getPaymentStatus());
            $payment->setReference($response->getClientReference());
            $payment->setMontant($response->getAmount());
            $payment->setCodePaymentOperateur($response->getTransactionId());
            $payment->setType("MOBILE_MONEY");
            $paymentRepository->add($payment, true);
            return $this->redirect($response->getWaveLaunchUrl());
        }
        else return $this->redirectToRoute('home');
    }

    #[Route(path: '/wave/checkout/{status}', name: 'wave_payment_callback')]
    public function wavePaymentCheckoutStatusCallback($status, Request $request,
                                                      PaymentRepository $paymentRepository): Response
    {
        $path = "/var/www/html/var/log";
        try{
            if(!file_exists($path)) {
                mkdir($path, 0777, true);
                file_put_contents($path . "/wave_payment_callback_$status" . date("YmdH:i:s") . ".log", $request->get("ref"));
            }
        }catch(\Exception $e){

        }

        $payment = $paymentRepository->findOneBy(["reference" => $request->get("ref")]);
        if ($payment && (strtoupper(trim($status)) === "SUCCESS")) {
            return $this->redirectToRoute('demande_display_receipt', [
                    "id" => $payment->getId(),
                    "status" => $status]
            );
        }else{
            return $this->redirectToRoute('home');
        }
    }

    #[Route(path: '/wave', name: 'wave_payment_checkout_webhook')]
    public function callbackWavePayment(Request $request,  PaymentRepository $paymentRepository, DemandeRepository $demandeRepository,): Response
    {
        $path = "/var/www/html/var/log";
        $payload =  json_decode($request->getContent(), true);
        if(!empty($payload) && array_key_exists("data", $payload)) {
            $data =  $payload['data'];
            if (!empty($data) && array_key_exists("client_reference", $data)) {
                $payment = $paymentRepository->findOneBy(["reference" => $data["client_reference"]]);
                if ($payment) {
                    $payment->setStatus(strtoupper($data["payment_status"]));
                    $payment->setMontant($data["amount"]);
                    $payment->setModifiedAt(new \DateTime());
                    $paymentRepository->add($payment, true);
                    if(array_key_exists("payment_status", $data)){
                        if(strtoupper($data["payment_status"]) === "SUCCEEDED"){
                            $demande = $payment->getDemande();
                            $demande->setStatus("PAYE");
                            $demande->setReceiptNumber($payment->getReceiptNumber());
                            $demandeRepository->add($demande, true);
                        }
                    }
                }
            }
        }

        try{

            if(!file_exists($path)) {
                mkdir($path, 0777, true);
                file_put_contents($path . "/wave_payment_checkout_webhook" . date("YmdH:i:s") . ".log", $request->getContent());
            }
        }catch(\Exception $e){
            return $this->json($payload);
        }

        return $this->json($payload);
    }




}
