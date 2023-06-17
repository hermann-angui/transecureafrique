<?php

namespace App\Controller\Admin;

use App\Entity\Macaron;
use App\Form\MacaronType;
use App\Helper\DataTableHelper;
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
        return $this->render('admin/macaron/index.html.twig');
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
    public function datatable(Request $request, Connection $connection, MacaronRepository $macaronRepository)
    {
        date_default_timezone_set("Africa/Abidjan");
        $params = $request->query->all();
        $paramDB = $connection->getParams();
        $table = 'macaron';
        $primaryKey = 'id';
        $macaron = null;
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
                'db' => 'numero_immatriculation',
                'dt' => 'numero_immatriculation'
            ],
            [
                'db' => 'date_de_premiere_mise_en_cirulation',
                'dt' => 'date_de_premiere_mise_en_cirulation'
            ],
            [
                'db' => 'date_d_edition',
                'dt' => 'date_d_edition'
            ],
            [
                'db' => 'identite_proprietaire',
                'dt' => 'identite_proprietaire'
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
                'db' => 'type_commercial',
                'dt' => 'type_commercial'
            ],
            [
                'db' => 'couleur_vehicule',
                'dt' => 'couleur_vehicule'
            ],
            [
                'db' => 'carroserie_vehicule',
                'dt' => 'carroserie_vehicule'
            ],
            [
                'db' => 'energie_vehicule',
                'dt' => 'energie_vehicule'
            ],
            [
                'db' => 'places_assises',
                'dt' => 'places_assises'
            ],
            [
                'db' => 'usage_vehicule',
                'dt' => 'usage_vehicule'
            ],
            [
                'db' => 'puissance_fiscale',
                'dt' => 'puissance_fiscale'
            ],
            [
                'db' => 'nombre_d_essieux',
                'dt' => 'nombre_d_essieux'
            ],
            [
                'db' => 'cylindree',
                'dt' => 'cylindree'
            ],
            [
                'db' => 'numero_vin_chassis',
                'dt' => 'numero_vin_chassis'
            ],
            [
                'db' => 'societe_de_credit',
                'dt' => 'societe_de_credit'
            ],
            [
                'db' => 'type_technique',
                'dt' => 'type_technique'
            ],
            [
                'db' => 'reference',
                'dt' => 'reference'
            ],
            [
                'db' => 'montant',
                'dt' => 'montant'
            ],
            [
                'db' => 'numero_d_immatriculation_precedent',
                'dt' => 'numero_d_immatriculation_precedent'
            ],
            [
                'db' => 'type',
                'dt' => 'type'
            ],
            [
                'db' => 'payment_type',
                'dt' => 'payment_type'
            ],
            [
                'db' => 'status',
                'dt' => 'status'
            ],
            [
                'db'        => 'id',
                'dt'        => 'id',
                'formatter' => function($d, $row) {
                    $id = $row['id'];
                    $content =  "<ul class='list-unstyled hstack gap-1 mb-0'>
                                      <li data-bs-toggle='tooltip' data-bs-placement='top' aria-label='View'>
                                          <a href='/admin/macaron/$id' class='btn btn-sm btn-soft-primary'><i class='mdi mdi-eye-outline'></i></a>
                                      </li>
                                      <li data-bs-toggle='tooltip' data-bs-placement='top' aria-label='Edit'>
                                         <a href='/admin/macaron/$id/edit' class='btn btn-sm btn-soft-success'><i class='mdi mdi-pencil-outline'></i></a>
                                      </li>
                                      <li data-bs-toggle='tooltip' data-bs-placement='top' aria-label='Supprimer'>
                                         <a href='/admin/macaron/$id/supprimer' class='btn btn-sm btn-soft-danger'><i class='mdi mdi-delete-alert-outline'></i></a>
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
        if(!empty($params['numero_carte_grise'])){
            $whereResult .= " numero_carte_grise LIKE '%". $params['numero_carte_grise'] . "%' AND";
        }
        if(!empty($params['numero_recepisse'])) {
            $whereResult .= " numero_recepisse LIKE '%". $params['numero_recepisse']. "%' AND";
        }
        if(!empty($params['numero_chassis'])) {
            $whereResult .= " numero_chassis LIKE '%". $params['numero_chassis']. "%' AND";
        }
        $whereResult = substr_replace($whereResult,'',-strlen(' AND'));
        $response = DataTableHelper::complex( $_GET, $sql_details, $table, $primaryKey, $columns, $whereResult);

        return new JsonResponse($response);
    }

    #[Route('/{id}', name: 'admin_macaron_show', methods: ['GET'])]
    public function show(Macaron $macaron): Response
    {
        return $this->render('admin/macaron/show.html.twig', [
            'macaron' => $macaron,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_macaron_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Macaron $macaron, MacaronService $macaronService): Response
    {
        date_default_timezone_set("Africa/Abidjan");
        $form = $this->createForm(MacaronType::class, $macaron);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $macaron->setStatus( $macaron->getStatus());
            $macaron->setReference( $macaron->getCompany());
            $macaron->setMontant( $macaron->getTitre());
            $macaronService->storeMacaron($macaron);

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
