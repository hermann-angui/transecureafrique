<?php

namespace App\Controller\Client;

use App\Entity\Demande;
use App\Entity\Payment;
use App\Repository\DemandeRepository;
use App\Repository\PaymentRepository;
use App\Service\Payment\PaymentService;
use App\Service\Wave\WaveCheckoutResponse;
use App\Service\Wave\WaveService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Uuid;
use function Symfony\Component\DependencyInjection\Loader\Configurator\env;


#[Route('/payment')]
class PaymentController extends AbstractController
{
    #[Route(path: '/do/{id}', name: 'do_payment')]
    public function doPayment(Request $request, Demande $demande,
                              WaveService $waveService,
                              DemandeRepository $demandeRepository,
                              PaymentRepository $paymentRepository): Response
    {
        if($demande->getPayment() && strtoupper($demande->getPayment()->getStatus()) === "SUCCEEDED") {
            return $this->redirectToRoute('demande_display_receipt', ["id" => $demande->getPayment()->getId(), "status" => "success"]);
        }

        $response = $waveService->makePayment($demande);

        if($response && !$demande->getPayment()) {
            $payment = new Payment();
            $payment->setStatus(strtoupper($response->getPaymentStatus()));
            $payment->setReference($response->getClientReference());
            $payment->setOperateur("WAVE");
            $payment->setMontant($response->getAmount());
            $payment->setType("MOBILE_MONEY");
            $payment->setReceiptNumber(PaymentService::generateReference());
            $payment->setDemande($demande);
            $payment->setCreatedAt(new \DateTime('now'));
            $payment->setModifiedAt(new \DateTime('now'));
            $paymentRepository->add($payment, true);
            $demande->setStatus(strtoupper($response->getPaymentStatus()));
            $demandeRepository->add($demande, true);
            return $this->redirect($response->getWaveLaunchUrl());
        }
        else return $this->redirectToRoute('home');

    }

    #[Route(path: '/wave/checkout/{status}', name: 'wave_payment_callback')]
    public function wavePaymentCheckoutStatusCallback($status,
                                                      Request $request,
                                                      DemandeRepository $demandeRepository,
                                                      PaymentRepository $paymentRepository): Response
    {
        $payment = $paymentRepository->findOneBy(["reference" => $request->get("ref")]);
        if ($payment && (strtoupper(trim($status)) === "SUCCESS")) {
            try{
                $path = "/var/www/html/var/log/wave_payment_callback/$status/";
                if(!file_exists($path)) mkdir($path, 0777, true);
                $data = ["reference" => $request->get("ref"), "date" => date("Ymd H:i:s")];
                file_put_contents($path . "log_". date("Ymd") . ".log", json_encode($data), FILE_APPEND);
            }catch(\Exception $e){
            }

            return $this->redirectToRoute('demande_display_receipt', ["id" => $payment->getId(), "status" => $status]);
        }else{
            $demande = $payment->getDemande();
            $payment->setDemande(null);
            $paymentRepository->remove($payment, true);
            return $this->redirectToRoute('demande_paiement', ['id' => $demande->getId() ]);
        }
    }

    #[Route(path: '/wave', name: 'wave_payment_checkout_webhook')]
    public function callbackWavePayment(Request $request,  PaymentRepository $paymentRepository, DemandeRepository $demandeRepository,): Response
    {
         $payload =  json_decode($request->getContent(), true);
        if(!empty($payload) && array_key_exists("data", $payload)) {
            $data =  $payload['data'];
            if (!empty($data) && array_key_exists("client_reference", $data)) {
                $payment = $paymentRepository->findOneBy(["reference" => $data["client_reference"]]);
                if ($payment) {
                    $payment->setStatus(strtoupper($data["payment_status"]));
                    $payment->setMontant($data["amount"]);
                    $payment->setModifiedAt(new \DateTime());
                    if(array_key_exists("transaction_id", $data) && isset($data["transaction_id"])) $payment->setCodePaymentOperateur($data["transaction_id"]);
                    $paymentRepository->add($payment, true);
                    if( array_key_exists("payment_status", $data) && (strtoupper($data["payment_status"]) === "SUCCEEDED") ){
                        $demande = $payment->getDemande();
                        $demande->setStatus("PAYE");
                        $demande->setReceiptNumber($payment->getReceiptNumber());
                        $demandeRepository->add($demande, true);
                    }
                    try{
                        $path = "/var/www/html/var/log/wave_payment_checkout_webhook/";
                        if(!file_exists($path)) mkdir($path, 0777, true);
                        file_put_contents($path . "log_" . date("YmdHis") . ".log", $request->getContent(), FILE_APPEND);

                    }catch(\Exception $e){
                    }
                }
            }
        }

        return $this->json($payload);
    }


}
