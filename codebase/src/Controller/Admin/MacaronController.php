<?php

namespace App\Controller\Admin;

use App\Entity\Demande;
use App\Entity\Macaron;
use App\Form\MacaronType;
use App\Helper\DataTableHelper;
use App\Repository\DemandeRepository;
use App\Repository\MacaronRepository;
use App\Service\Macaron\MacaronService;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('admin/macaron')]
class MacaronController extends AbstractController
{

    #[Route('', name: 'admin_macaron_index', methods: ['GET'])]
    public function index(Request $request, MacaronRepository $macaronRepository): Response
    {
        $macarons = $macaronRepository->findAll();
        return $this->render('admin/macaron/index.html.twig', ["macarons" => $macarons]);
    }

    #[Route('/generate/{id}', name: 'admin_macaron_generate', methods: ['GET', 'POST'])]
    public function generate(Request $request, Demande $demande, MacaronRepository $macaronRepository, DemandeRepository $demandeRepository): Response
    {
        if(!$demande->getPayment()) return $this->json('no_payment');
        if(!$demande->getNumeroTelephoneProprietaire()) return $this->json('no_telproprio');
        if(!$demande->getMacaronQrcodeNumber()) return $this->json('no_qrcode');

        if($request->get('doc') === 'carte_grise' && !$demande->getCarteGriseImage()){
            return $this->json('no_carte_grise_image');
        }
        if($request->get('doc') === 'recepisse' && !$demande->getRecepisseImage()){
            return $this->json('no_recepisse_image');
        }

        $macaron = $demande->getMacaron() ?:new Macaron();
        if($macaron){
         //   $macaron->setLastEditor($this->getUser());
            $macaron->setReference($demande->getReference());
            $macaron->setMacaronQrcodeNumber($demande->getMacaronQrcodeNumber());
            $macaron->setStatus("COMPLETED");
            $macaron->setDemande($demande);
            $macaron->setNumeroTelephoneProprietaire($demande->getNumeroTelephoneProprietaire());
            $macaron->setValidityFrom(new \DateTime());
            $macaron->setValidityTo(new \DateTime('last day of December this year'));
            $macaron->setCreatedAt(new \DateTime());
            $macaron->setModifiedAt(new \DateTime());
            $macaronRepository->add($macaron, true);

            $demande->setStatus("CLOSED");
            $demandeRepository->add($demande, true);
        }

        return $this->json("ok");
    }

    #[Route('/new', name: 'admin_macaron_new', methods: ['GET', 'POST'])]
    public function new(Request $request, MacaronService $macaronService): Response
    {
        $macaron = new Macaron();
        $form = $this->createForm(MacaronType::class, $macaron);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            $data = $request->request->all();
            $macaronService->createMacaron($macaron);
            return $this->redirectToRoute('admin_macaron_index');
        }
        return $this->renderForm('admin/macaron/new.html.twig', [
            'macaron' => $macaron,
            'form' => $form,
        ]);
    }

    #[Route('/macaron/dt', name: 'admin_macaron_dt', methods: ['GET'])]
    public function datatable(Request $request, Connection $connection)
    {
        date_default_timezone_set("Africa/Abidjan");

        $user = $this->getUser();

        $params = $request->query->all();
        $paramDB = $connection->getParams();
        $table = 'macaron';
        $primaryKey = 'id';
        $macaron = null;
        $columns = [
            [
                'db' => 'id',
                'dt' => 'DT_RowId',
                'formatter' => function( $d, $row ) {
                    return 'row_'.$d;
                }
            ],
            [
                'db' => 'reference',
                'dt' => 'reference'
            ],
            [
                'db' => 'numero_telephone_proprietaire',
                'dt' => 'numero_telephone_proprietaire'
            ],
            [
                'db' => 'macaron_qrcode_number',
                'dt' => 'macaron_qrcode_number',
            ],
            [
                'db' => 'status',
                'dt' => 'status'
            ],
            [
                'db' => 'validity_to',
                'dt' => 'validity_to'
            ],
            [
                'db' => 'validity_from',
                'dt' => 'validity_from'
            ],
            [
                'db'        => 'id',
                'dt'        => '',
                'formatter' => function($d, $row) use ($user) {
                    $id = $row['id'];
                    $content =  "<ul class='list-unstyled hstack gap-1 mb-0'>";

                    $content.=  "<li data-bs-toggle='tooltip' data-bs-placement='top' aria-label='View'>
                                    <a href='/admin/macaron/$id' class='btn btn-sm btn-soft-primary'><i class='mdi mdi-eye-outline'></i></a>
                                  </li>";
                    if(in_array('ROLE_SUPER_ADMIN', $user->getRoles())){
                        $content .= "<li data-bs-toggle='tooltip' data-bs-placement='top' aria-label='View'>
                                       <a href='/admin/macaron/$id/edit' class='btn btn-sm btn-soft-primary'><i class='mdi mdi-pen'></i></a>
                                     </li>";
                     }

                    $content.= "</ul>";
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
        if(!empty($params['numero_carte_grise'])){
            $whereResult .= " numero_carte_grise LIKE '%". $params['numero_carte_grise'] . "%' AND";
        }
        $whereResult = substr_replace($whereResult,'',-strlen(' AND'));
        $response = DataTableHelper::complex( $_GET, $sql_details, $table, $primaryKey, $columns, $whereResult);

        return new JsonResponse($response);
    }

    #[Route('/macaron/demande/dt', name: 'admin_macaron_demande_dt', methods: ['GET'])]
    public function macaronDemandeDT(Request $request, Connection $connection, DemandeRepository $demandeRepository)
    {
        $user = $this->getUser();

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
                'db' => 'date_rendez_vous',
                'dt' => 'date_rendez_vous'
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
                'db' => 'receipt_number',
                'dt' => 'receipt_number'
            ],
            [
                'db'        => 'id',
                'dt'        => '',
                'formatter' => function($d, $row) use($user) {
                    $id = $row['id'];
                    $content =  "<ul class='list-unstyled hstack gap-1 mb-0'>";
                    $content.=  "<li data-bs-toggle='tooltip' data-bs-placement='top' aria-label='View'>
                                    <a href='/admin/demande/$id' class='btn btn-sm btn-soft-primary'><i class='mdi mdi-eye-outline'></i></a>
                                  </li>";

                    if(in_array('ROLE_SUPER_ADMIN', $user->getRoles())){
                        $content .= "<li data-bs-toggle='tooltip' data-bs-placement='top' aria-label='View'>
                                       <a href='/admin/demande/$id/edit' class='btn btn-sm btn-soft-primary'><i class='mdi mdi-pen'></i></a>
                                     </li>";
                    }

                    if(in_array('ROLE_SUPER_ADMIN', $user->getRoles())){
                        $content .= "<li data-bs-toggle='tooltip' data-bs-placement='top' aria-label='View'>
                                       <a href='/admin/demande/$id/delete' class='btn btn-sm btn-soft-primary'><i class='mdi mdi-trash-can'></i></a>
                                     </li>";
                    }
                    $content.= "</ul>";
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

        $whereResult = " (status = 'CLOSED') ";
/*
        if(!empty($params["quicksearch"])){
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

            $whereResult .= " (status = 'CLOSED') ";

        }
*/

        $response = DataTableHelper::complex( $_GET, $sql_details, $table, $primaryKey, $columns, trim($whereResult));

        return new JsonResponse($response);
    }
    #[Route('/{id}', name: 'admin_macaron_show', methods: ['GET'])]
    public function show(Macaron $macaron): Response
    {
        return $this->render('admin/macaron/show.html.twig', [
            'macaron' => $macaron,
            'payment' => $macaron?->getDemande()?->getPayment(),
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_macaron_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Macaron $macaron, MacaronService $macaronService): Response
    {
        date_default_timezone_set("Africa/Abidjan");
        $form = $this->createForm(MacaronType::class, $macaron);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $macaronService->store($macaron);
            return $this->redirectToRoute('admin_macaron_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/macaron/edit.html.twig', [
            'macaron' => $macaron,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/supprimer', name: 'admin_macaron_delete', methods: ['GET','POST'])]
    public function delete(Request $request, Macaron $macaron, MacaronRepository $macaronRepository): Response
    {
        if ( true /* $this->isCsrfTokenValid('delete'.$macaron->getId(), $request->request->get('_token')) */ ) {
            $macaronRepository->remove($macaron, true);
            $fileName = "/var/www/html/public/macaron/" . $macaron->getNumeroVinChassis() . "/";
            if(file_exists($fileName)) {
                $fs =  new Filesystem();
                $fs->remove($fileName);
            }
        }
        return $this->redirectToRoute('admin_macaron_index', [], Response::HTTP_SEE_OTHER);
    }

}
