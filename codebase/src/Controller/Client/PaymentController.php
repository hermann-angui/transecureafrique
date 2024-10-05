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
            $payment->addDemande($demande);
            $payment->setCreatedAt(new \DateTime('now'));
            $payment->setModifiedAt(new \DateTime('now'));
            $paymentRepository->add($payment, true);

            $demande->setStatus(strtoupper($response->getPaymentStatus()));
            $demandeRepository->add($demande, true);
            return $this->redirect($response->getWaveLaunchUrl());
        }
        else return $this->redirectToRoute('home');
    }

    #[Route(path: '/multiple/do', name: 'do_payment_multiple')]
    public function doPaymentMultiple(Request $request,
                                      WaveService $waveService,
                                      DemandeRepository $demandeRepository,
                                      PaymentRepository $paymentRepository): Response
    {
        $data = $request->request->all();
        $demandes = $demandeRepository->findBy(['groupe' => 1, 'groupe_id' => $data['group_id']]);
        if(!empty($demandes)) {
            $total = 0;
            foreach ($demandes as $demande) {
                if ($demande->getPayment() && strtoupper($demande->getPayment()->getStatus()) === "SUCCEEDED") continue;
                $total+= $demande->getMontant();
            }

            $response = $waveService->makeMultilePayment($data['groupe_id'], $total);

            if ($response && !$demande->getPayment()) {
                $payment = new Payment();
                $payment->setStatus(strtoupper($response->getPaymentStatus()));
                $payment->setReference($response->getClientReference());
                $payment->setOperateur("WAVE");
                $payment->setMontant($response->getAmount());
                $payment->setType("MOBILE_MONEY");
                $payment->setGroupe(1);
                $payment->addDemande($demande);
                $payment->setGroupeId($data['groupe_id']);
                $payment->setReceiptNumber(PaymentService::generateReference());
                $payment->setCreatedAt(new \DateTime('now'));
                $payment->setModifiedAt(new \DateTime('now'));
                $paymentRepository->add($payment, true);
            }
            foreach ($demandes as $demande) {
                $demande->setStatus(strtoupper($response->getPaymentStatus()));
                $demande->setPayment($payment);
                $demandeRepository->add($demande, true);
                return $this->redirect($response->getWaveLaunchUrl());
            }
        }
       return $this->redirectToRoute('home');
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
            return $this->redirectToRoute('home');
           // return $this->redirectToRoute('demande_paiement', ['id' => $demande->getId() ]);
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
                if ($payment && !in_array($payment->getStatus(), ['CLOSED','SUCCEEDED'])) {
                    $payment->setStatus(strtoupper($data["payment_status"]));
                    $payment->setMontant($data["amount"]);
                    $payment->setModifiedAt(new \DateTime());

                    // Appointment is after two day if saturday or sunday postpone to monday
                    $appointmentDate = new \DateTime();
                    $appointmentDate->modify("+2 day");
                    $d = $appointmentDate->format('N');
                    if($d == 6) $appointmentDate->modify("+2 day");
                    if($d == 7) $appointmentDate->modify("+1 day ");
                    $payment->setDateRendezVous($appointmentDate);

                    if(array_key_exists("transaction_id", $data) && isset($data["transaction_id"])) $payment->setCodePaymentOperateur($data["transaction_id"]);

                    $paymentRepository->add($payment, true);

                    if( array_key_exists("payment_status", $data) && (strtoupper($data["payment_status"]) === "SUCCEEDED") ){
                        $demandes = $payment->getDemandes();
                        foreach($demandes as $demande){
                            $demande->setStatus("PAYE");
                            $demande->setReceiptNumber($payment->getReceiptNumber());
                            $demande->setDateRendezVous($payment->getDateRendezVous());
                            $demandeRepository->add($demande, true);
                        }
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
