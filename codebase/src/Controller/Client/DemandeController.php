<?php

namespace App\Controller\Client;

use App\Entity\Demande;
use App\Entity\OtpCode;
use App\Entity\Payment;
use App\Helper\DataTableHelper;
use App\Repository\DemandeRepository;
use App\Repository\PaymentRepository;
use App\Service\Demande\DemandeService;
use App\Service\Otp\OtpService;
use App\Service\Payment\PaymentService;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Uuid;

#[Route(path: 'demande')]
class DemandeController extends AbstractController
{
    #[Route(path: '/formulaire', name: 'demande', methods: ['POST', 'GET'])]
    public function demandeAdv(Request $request): Response
    {
        if($request->getMethod() === "POST") {
            $data = $request->request->all();
            if(empty($data['otpcode'])) return $this->redirectToRoute('auth');
            if(array_key_exists('multiple_demande', $data) && $data['multiple_demande'] === "1") {
                return $this->render('frontend/bs/multiple-demande.html.twig', [
                        "demande_type" => $data['demande_type'],
                        "otpcode" => $data['otpcode'],
                        "group_id" => Uuid::v4()->toRfc4122(),
                        "multiple_demande" => $data['multiple_demande']
                    ]
                );
            }else{
               if(!array_key_exists('demande_type', $data) || !array_key_exists('otpcode', $data)) return $this->redirectToRoute('auth');
                return $this->render('frontend/bs/formulaire.html.twig', [
                        "demandeType" => $data['demande_type'],
                        "otpcode" => $data['otpcode']
                    ]
                );
            }
        }
        return $this->redirectToRoute('auth');
    }
    #[Route(path: '/formulaire/save', name: 'demande_save', methods: ['POST', 'GET'])]
    public function demande(Request $request,
                            PaymentRepository $paymentRepository,
                            DemandeService $demandeService): Response
    {
        if($request->getMethod() === "POST") {
            $data = $request->request->all();
            if(empty($data['otpcode'])) return $this->redirectToRoute('auth');
            $duplicateDemande= $demandeService->checkDuplicateEntry($data, $paymentRepository);
            if(is_array($duplicateDemande) && !empty($res)) {
                $res["info"] = "duplicate";
                return $this->json($res);
            }
            if($duplicateDemande instanceof Demande)  $demande = $demandeService->update($duplicateDemande, $data);
            else $demande = $demandeService->create($data);
            $d = [
                "id" => $demande->getId(),
                "numero_carte_grise" => $demande->getNumeroCarteGrise(),
                "numero_immatriculation" => $demande->getNumeroImmatriculation(),
                "numero_vin_chassis" => $demande->getNumeroVinChassis(),
                "numero_recepisse" => $demande->getNumeroRecepisse(),
                "montant" => $demande->getMontant()
            ];
            return $this->json(["info" => "success", "demandeid" => $demande->getId(), "data" => $d]);
        }
        return $this->redirectToRoute('auth');
    }

    #[Route(path:'/select/{id}', name: "select_demande_type")]
    public  function selectDemandeType(OtpCode $otpCode, Request $request)
    {
        return $this->render('frontend/bs/select-demande-type.html.twig', [
            "optcode" => $otpCode
        ]);
    }
    #[Route('/historique/dt', name: 'demande_history_dt', methods: ['GET'])]
    public function datatable(Request $request, Connection $connection, DemandeRepository $demandeRepository)
    {
        date_default_timezone_set("Africa/Abidjan");
        $params = $request->query->all();
        $paramDB = $connection->getParams();
        $table = 'demande';
        $primaryKey = 'id';
        $demande = null;
        $columns = [
            [
                'db' => 'numero_carte_grise',
                'dt' => 'numero_carte_grise',
            ],
            [
                'db' => 'numero_recepisse',
                'dt' => 'numero_recepisse',
            ],
            [
                'db' => 'numero_immatriculation',
                'dt' => 'numero_immatriculation'
            ],
            [
                'db' => 'numero_vin_chassis',
                'dt' => 'numero_vin_chassis'
            ],
            [
                'db' => 'montant',
                'dt' => 'montant'
            ],
            [
                'db' => 'status',
                'dt' => 'status',
                'formatter' => function($d, $row) {
                    switch($d) {
                        case "PROCESSING":
                            return "<span class='badge rounded-pill text-bg-info'>EN ATTENTE</span>";
                        case "PAYE":
                            return "<span class='badge rounded-pill text-bg-success'>PAYE</span>";
                        case "CLOSED":
                            return "<span class='badge rounded-pill text-bg-dark'>TERMINE</span>";
                        default:
                            return "<span class='badge rounded-pill text-bg-info'>EN ATTENTE</span>";
                    }

                }
            ],
            [
                'db'        => 'id',
                'dt'        => '',
                'formatter' => function($d, $row) use($demandeRepository) {
                    $id = $row['id'];
                    $content =  "<ul class='list-unstyled hstack gap-1 mb-0'>";
                    if(!in_array($row['status'], ["PAYE", "CLOSED"])) $content .= "<li><a href='/demande/formulaire/edit/$id' class='btn btn-sm btn-info text-white'><i class='mdi mdi-pen'></i> Poursuivre la demande</a></li>";
                    else {
                        $demande = $demandeRepository->find($id);
                        $payment_id = $demande->getPayment()->getId();
                        $content .= "<li><a href='/demande/receipt-pdf/$payment_id' class='btn btn-sm btn-dark'><i class='mdi mdi-printer'></i> Imprimer le recu</a></li>";
                    }
                    $content .= "</ul>";
                    return $content;
                }
            ]
        ];

        $sql_details = array(
            'user' => $paramDB['user'],
            'pass' => $paramDB['password'],
            'db'   => $paramDB['dbname'],
            'host' => $paramDB['host']
        );

        $whereResult = '';
        if(!empty($params['optcode'])){
            $whereResult .= " otp_code_id = '". trim($params['optcode']) . "' AND ";
        }

    //    $whereResult .= " (status = 'PROCESSING' OR status IS NULL)";

        $whereResult .= trim($whereResult, 'AND ');

        $response = DataTableHelper::complex( $_GET, $sql_details, $table, $primaryKey, $columns, trim($whereResult));

        return new JsonResponse($response);
    }

    #[Route(path:'/historique/{id}', name: "demande_history")]
    public  function demandeHistory(OtpCode $otpCode, Request $request, OtpService $otpService , DemandeRepository $demandeRepository)
    {
        if($otpCode){
            return $this->render('frontend/bs/user-demande-history.html.twig', [
                "optCode" => $otpCode
            ]);
        }
        return $this->redirectToRoute('auth');
    }

    #[Route(path: '/formulaire/edit/{id}', name: 'demande_edit', methods: ['POST', 'GET'])]
    public function formulaireEditDemande(?Demande $demande, Request $request,
                                          DemandeService $demandeService,
                                          PaymentRepository $paymentRepository): Response
    {
        if ($request->getMethod() === "GET") {
            $payment = $demande->getPayment();
            if(!$demande->getGroupe() && $payment) {
                $demande->setPayment(null);
                $demande->setStatus(null);
                $demandeService->save($demande);
                $paymentRepository->remove($payment, true);
            }
            return $this->render('frontend/bs/formulaire_edit.html.twig', ['demande' => $demande]);
        } elseif ($request->getMethod() === "POST") {
            $data = $request->request->all();
            $res = $demandeService->update($demande, $data);
            if(is_array($res) && !empty($res)) return $this->json('duplicate');
            elseif($res instanceof Demande) return $this->json($res->getId());
            else return $this->json('nodata');
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
        if(in_array($payment->getStatus(), ["SUCCEEDED", "PAYE", "CLOSED"])){
            $paymentService->generateReceipt($payment);
            return $this->render('frontend/bs/display-receipt.html.twig', ['payment' => $payment]);
        }
        return $this->redirectToRoute('auth');
    }

    #[Route(path: '/payment/{id}', name: 'demande_paiement', methods: ['POST', 'GET'])]
    public function demandePayment(Demande $demande, Request $request, PaymentService $paymentService, DemandeService $demandeService): Response
    {
        return $this->render('frontend/bs/payment.html.twig', [
            "demande" => $demande
        ]);
    }

    #[Route(path: '/payment/multiple/{group_id}', name: 'demande_multiple_paiement', methods: ['POST', 'GET'])]
    public function demandeMultiplePayment($group_id, Request $request, DemandeRepository $demandeRepository): Response
    {
        $demandes = $demandeRepository->findBy(['groupe' => 1, 'groupeId' => $group_id]);
        if(!empty($demandes) && !empty($group_id)){
            $total = 0;
            foreach($demandes as $demande){
                $total+= $demande->getMontant();
            }
            return $this->render('frontend/bs/payment-multiple.html.twig', [
                "montant" => $total,
                "group_id" => $group_id,
                "demandes" => $demandes
            ]);
        }

        return $this->redirectToRoute('auth');
    }
    #[Route(path: '/search', name: 'demande_search')]
    public function searchDemande(Request $request, DemandeRepository $demandeRepository, PaymentRepository $paymentRepository): Response
    {
        $term = $request->get('search_receipt_term');
        $criteria = $request->get('search_receipt_criteria');

        if($term && $criteria) {
            if ($criteria === 'numero_immatriculation') $demande = $demandeRepository->findOneBy(['numero_immatriculation' => $term]);
            if ($criteria === 'numero_chassis') $demande = $demandeRepository->findOneBy(['numero_vin_chassis' => $term]);
            if ($criteria === 'numero_recu') $payment = $paymentRepository->findOneBy(['receipt_number' => $term]);
            if ($payment->getStatus()==="SUCCEEDED") {
                return $this->redirectToRoute('demande_display_receipt', ['id' => $payment->getId()]);
            } else {
                $warning = "Votre demande est en cours de traitement ....";
                return $this->render('frontend/bs/search-demande.html.twig', ["warning" => $warning]);
            }
        }
         return $this->render('frontend/bs/search-demande.html.twig');
    }

    #[Route('/receipt-pdf/{id}', name: 'download_receipt_pdf', methods: ['GET'])]
    public function pdfGenerate(Payment $payment, PaymentService $paymentService): Response {
        return $paymentService->downloadPdfReceipt($payment);
    }

    #[Route('/{id}/delete', name: 'demande_delete', methods: ['GET','POST'])]
    public function delete(Request $request, Demande $demande, DemandeRepository $demandeRepository): Response
    {
        return $this->json('OK');
    }

    #[Route('/check_duplicate_entry', name: 'check_duplicate_entry', methods: ['GET','POST'])]
    public function checkDuplicateEntry(Request $request, DemandeRepository $demandeRepository): Response
    {
        if($request->get('numero_recepisse')){
            $demande = $demandeRepository->findOneBy(['numero_recepisse' => $request->get('numero_recepisse')]);
            if($demande) return $this->json('duplicate');
        }
        if($request->get('numero_carte_grise')){
            $demande = $demandeRepository->findOneBy(['numero_carte_grise' => $request->get('numero_carte_grise')]);
            if($demande) return $this->json('duplicate');
        }
        if($request->get('numero_vin_chassis')){
            $demande = $demandeRepository->findOneBy(['numero_vin_chassis' => $request->get('numero_vin_chassis')]);
            if($demande) return $this->json('duplicate');
        }
        if($request->get('numero_immatriculation')){
            $demande = $demandeRepository->findOneBy(['numero_immatriculation' => $request->get('numero_immatriculation')]);
            if($demande) return $this->json('duplicate');
        }
        return $this->json('noduplicate');
    }
}
