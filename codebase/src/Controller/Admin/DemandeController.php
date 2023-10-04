<?php

namespace App\Controller\Admin;

use App\Entity\Demande;
use App\Form\DemandeType;
use App\Helper\DataTableHelper;
use App\Repository\DemandeRepository;
use App\Repository\MacaronRepository;
use App\Repository\PaymentRepository;
use App\Service\Demande\DemandeService;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('admin/demande')]
class DemandeController extends AbstractController
{
    #[Route('', name: 'admin_demande_index', methods: ['GET'])]
    public function index(Request $request,
                          PaymentRepository $paymentRepository,
                          MacaronRepository $macaronRepository): Response
    {
        $stats = [
            "macarons" => $macaronRepository->count([]),
            "demandes" => $paymentRepository->count([]),
            "daily" => $paymentRepository->findTotalDaily(),
            "weekly" => $paymentRepository->findTotalWeekly(),
        ];
        return $this->render('admin/demande/index.html.twig', ["stats"=> $stats]);
    }

    #[Route('/new', name: 'admin_demande_new', methods: ['GET', 'POST'])]
    public function new(Request $request, DemandeService $demandeService): Response
    {
        $demande = new Demande();
        $form = $this->createForm(DemandeType::class, $demande);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            $data = $request->request->all();
            $demandeService->create($data);
            return $this->redirectToRoute('admin_demande_index');

        }
        return $this->renderForm('admin/demande/new.html.twig', [
            'demande' => $demande,
            'form' => $form,
        ]);
    }

    #[Route('/demande/dt', name: 'admin_demande_dt', methods: ['GET'])]
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
                'db' => 'id',
                'dt' => 'DT_RowId',
                'formatter' => function( $d, $row ) {
                    return 'row_'.$d;
                }
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
                'db' => 'numero_immatriculation',
                'dt' => 'numero_immatriculation'
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
                'db' => 'numero_vin_chassis',
                'dt' => 'numero_vin_chassis'
            ],
            [
                'db' => 'receipt_number',
                'dt' => 'receipt_number'
            ],
            [
                'db'        => 'id',
                'dt'        => '',
                'formatter' => function($d, $row) {
                    $id = $row['id'];
                    $content =  "<ul class='list-unstyled hstack gap-1 mb-0'>
                                      <li data-bs-toggle='tooltip' data-bs-placement='top' aria-label='View'>
                                          <a href='/admin/demande/$id' class='btn btn-sm btn-soft-primary'><i class='mdi mdi-eye-outline'></i></a>
                                      </li>
                                </ul>";
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

        if(!empty($params["quicksearch"])){
            //  $params["search"]["value"] = $params["quicksearch"];
            //  $params["search"]["regex"] = "true";
            $fullSearchValue = trim($params['quicksearch']);
            $whereResult .= "( receipt_number LIKE '%" . $fullSearchValue . "%' OR ";
            $whereResult .= " numero_carte_grise LIKE '%" . $fullSearchValue. "%' OR ";
            $whereResult .= " numero_recepisse LIKE '%" . $fullSearchValue. "%' OR ";
            $whereResult .= " numero_immatriculation LIKE '%" . $fullSearchValue . "%' OR ";
            $whereResult .= " numero_vin_chassis LIKE '%" . $fullSearchValue . "%' )";
         //   $whereResult .= " AND (status = 'PAYE') ";
        }else{
            if(!empty($params['numero_carte_grise'])){
                $whereResult .= " numero_carte_grise LIKE '%". trim($params['numero_carte_grise']) . "%' AND";
            }
            if(!empty($params['numero_recepisse'])) {
                $whereResult .= " numero_recepisse LIKE '%". trim($params['numero_recepisse']) . "%' AND";
            }
            if(!empty($params['numero_immatriculation'])) {
                $whereResult .= " numero_immatriculation LIKE '%". trim($params['numero_immatriculation']) . "%' AND";
            }
            if(!empty($params['numero_vin_chassis'])) {
                $whereResult .= " numero_vin_chassis LIKE '%". trim($params['numero_vin_chassis']) . "%' AND";
            }
            if(!empty($params['identite_proprietaire'])) {
                $whereResult .= " identite_proprietaire LIKE '%". trim($params['identite_proprietaire']) . "%' AND";
            }
            if(!empty($params['identite_proprietaire_piece'])) {
                $whereResult .= " identite_proprietaire_piece LIKE '%". trim($params['identite_proprietaire_piece']) . "%' AND";
            }
            if(!empty($params['numero_telephone_proprietaire'])) {
                $whereResult .= " numero_telephone_proprietaire LIKE '%". trim($params['numero_telephone_proprietaire']) . "%' AND";
            }
            if(!empty($params['receipt_number'])) {
                $whereResult .= " receipt_number LIKE '%". trim($params['receipt_number']) . "%'";
            }
          //  $whereResult .= " status != 'SUCCEEDED'";
        }

       // $whereResult = substr_replace($whereResult,'',-strlen(' AND'));
        $response = DataTableHelper::complex( $_GET, $sql_details, $table, $primaryKey, $columns, trim($whereResult));

        return new JsonResponse($response);
    }

    #[Route('/{id}', name: 'admin_demande_show', methods: ['GET'])]
    public function show(Demande $demande): Response
    {
        if(!$demande->getPayment()) return $this->redirectToRoute('admin_index');
        return $this->render('admin/demande/show.html.twig', [
            'demande' => $demande,
            'document' => $demande->getNumeroCarteGrise() ? 'carte_grise': 'recepisse'
        ]);
    }

    #[Route('/{id}/edit/ajax', name: 'admin_demande_edit_ajax', methods: ['GET', 'POST'])]
    public function editAjax(Request $request, Demande $demande, DemandeService $demandeService): Response
    {
        $data = [];
        $payload = $request->request->all();
        if(!empty($payload)) $data[$payload["name"]] = $payload["value"];

        $files = $request->files->all();
        if(!empty($files)){
            foreach($files as $key => $value){
                $data[$key] = $value;
            }
        }

    // $demande->setLastEditor($this->getUser());
        $result = $demandeService->update($demande, $data);
        if($result instanceof Demande) return $this->json("ok");
        elseif(is_string($result)) return $this->json($result);
        else return $this->json("error");
    }

    #[Route('/{id}/edit', name: 'admin_demande_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Demande $demande, DemandeService $demandeService): Response {

        date_default_timezone_set("Africa/Abidjan");
        $form = $this->createForm(DemandeType::class, $demande);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
             // $demandeService->update($demande, $form->getData());
             $demandeService->save($demande);
             return $this->redirectToRoute('admin_demande_index', [], Response::HTTP_SEE_OTHER);
        }
        return $this->renderForm('admin/demande/edit.html.twig', ['demande' => $demande, 'form' => $form]);
    }

    #[Route('/{id}/supprimer', name: 'admin_demande_delete', methods: ['GET','POST'])]
    public function delete(Request $request, Demande $demande, DemandeRepository $demandeRepository): Response
    {
        if ( true /* $this->isCsrfTokenValid('delete'.$demande->getId(), $request->request->get('_token')) */ ) {
            $demandeRepository->remove($demande, true);
            $fileName = "/var/www/html/public/demande/" . $demande->getNumeroVinChassis() . "/";
            if(file_exists($fileName)) {
                $fs =  new Filesystem();
                $fs->remove($fileName);
            }
        }
        return $this->redirectToRoute('admin_demande_index', [], Response::HTTP_SEE_OTHER);
    }

}
