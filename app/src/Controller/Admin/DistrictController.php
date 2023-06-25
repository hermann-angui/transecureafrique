<?php

namespace App\Controller\Admin;

use App\Entity\Demande;
use App\Entity\Macaron;
use App\Form\DemandeType;
use App\Form\MacaronType;
use App\Helper\DataTableHelper;
use App\Repository\DemandeRepository;
use App\Repository\MacaronRepository;
use App\Service\Demande\DemandeService;
use App\Service\Macaron\MacaronService;
use App\Service\Payment\PaymentService;
use Doctrine\DBAL\Connection;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Knp\Snappy\Pdf;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('admin/district')]
class DistrictController extends AbstractController
{

    #[Route('', name: 'admin_district_index', methods: ['GET'])]
    public function index(Request $request,
                          PaymentService $paymentService,
                          DemandeService $demandeService,
                          MacaronService $macaronService): Response
    {
        $stats = [
            "total_demandes" => $paymentService->getTotalDemandes(),
            "total_macarons_en_circulation" => $macaronService->getMacaronEnCirulation(),
            "total_macarons_non_delivres" => $demandeService->getTotalPendingPayment(),          // If not payed the macaron is not generated and not delivered
            "total_demandes_journalieres" => $paymentService->getTotalDailyDemande(),
            "total_demandes_hebdomadaires" => $paymentService->getTotalWeeklyDemandes(),
        ];
        return $this->render('admin/district/index.html.twig', $stats);
    }

    #[Route('/data', name: 'admin_dstrict_dt', methods: ['GET'])]
    public function datatable(Request $request, Connection $connection)
    {
        date_default_timezone_set("Africa/Abidjan");
        $params = $request->query->all();
        $paramDB = $connection->getParams();
        $table = 'demande';
        $primaryKey = 'id';
        $columns = [
            [
                'db' => 'id',
                'dt' => 'id',
            ],
            [
                'db' => 'numero_carte_grise',
                'dt' => 'numero_carte_grise',
            ],
            [
                'db' => 'numero_recepisse',
                'dt' => 'numero_recepisse',
            ],
            [
                'db' => 'marque_du_vehicule',
                'dt' => 'marque_du_vehicule'
            ],
            [
                'db' => 'genre_vehicule',
                'dt' => 'genre_vehicule'
            ],
            [
                'db' => 'identite_proprietaire',
                'dt' => 'identite_proprietaire'
            ],
            [
                'db' => 'identite_proprietaire_piece',
                'dt' => 'identite_proprietaire_piece'
            ],
            [
                'db' => 'energie_vehicule',
                'dt' => 'energie_vehicule'
            ],
        ];

        $sql_details = array(
            'user' => $paramDB['user'],
            'pass' => $paramDB['password'],
            'db'   => $paramDB['dbname'],
            'host' => $paramDB['host']
        );

        $whereResult = '';
        if(!empty($params['numero_carte_grise'])){
            $whereResult .= " numero_carte_grise LIKE '%". $params['numero_carte_grise'] . "%' AND";
        }
        if(!empty($params['numero_recepisse'])) {
            $whereResult .= " numero_recepisse LIKE '%". $params['numero_recepisse']. "%' AND";
        }
        if(!empty($params['marque_du_vehicule'])) {
            $whereResult .= " marque_du_vehicule LIKE '%". $params['marque_du_vehicule']. "%' AND";
        }
        if(!empty($params['genre_vehicule'])) {
            $whereResult .= " genre_vehicule LIKE '%". $params['genre_vehicule']. "%' AND";
        }
        if(!empty($params['energie_vehicule'])) {
            $whereResult .= " energie_vehicule LIKE '%". $params['energie_vehicule']. "%' AND";
        }
        if(!empty($params['identite_proprietaire'])) {
            $whereResult .= " identite_proprietaire LIKE '%". $params['identite_proprietaire']. "%' AND";
        }
        if(!empty($params['identite_proprietaire_piece'])) {
            $whereResult .= " identite_proprietaire_piece LIKE '%". $params['identite_proprietaire_piece']. "%' AND";
        }
        $whereResult .= " payment_id IS NOT NULL";

        $response = DataTableHelper::complex( $_GET, $sql_details, $table, $primaryKey, $columns, $whereResult);

        return new JsonResponse($response);
    }

    #[Route('/{id}', name: 'admin_dstrict_show', methods: ['GET'])]
    public function show(Demande $demande): Response
    {
        return $this->render('admin/district/show.html.twig', [
            'demande' => $demande,
        ]);
    }


}
