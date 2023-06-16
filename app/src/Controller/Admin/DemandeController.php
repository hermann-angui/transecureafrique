<?php

namespace App\Controller\Admin;

use App\Entity\Demande;
use App\Entity\Macaron;
use App\Form\DemandeMacaronType;
use App\Helper\DataTableHelper;
use App\Repository\DemandeRepository;
use App\Repository\MacaronRepository;
use Doctrine\DBAL\Connection;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Knp\Snappy\Pdf;
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
    public function index(Request $request, DemandeRepository $demandeRepository): Response
    {
        return $this->render('admin/demande/index.html.twig');
    }

    #[Route('/pdf/{id}', name: 'admin_pdf', methods: ['GET'])]
    public function pdfGenerate(Request $request, Macaron $demande, Pdf $knpSnappyPdf): Response
    {
        $html = $this->renderView('admin/pdf/public_profile.html.twig', array(
            'demande'  => $demande
        ));

        return new PdfResponse(
            $knpSnappyPdf->getOutputFromHtml($html),
            'file.pdf'
        );
    }


    #[Route('/new', name: 'admin_demande_new', methods: ['GET', 'POST'])]
    public function new(Request $request, DemandeService $demandeService): Response
    {
        $demande = new Demande();
        $form = $this->createForm(DemandeType::class, $demande);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            $data = $request->request->all();
            $demandeService->createDemande($demande);
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
                'db' => 'subscription_expire_date',
                'dt' => 'subscription_expire_date'
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
                'db' => 'qrcode',
                'dt' => 'qrcode'
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
                'db'        => 'payment_type',
                'dt'        => 'payment_type',
                'formatter' => function($d, $row) {
                    $id = $row['id'];
                    $content =  "<ul class='list-unstyled hstack gap-1 mb-0'>
                                      <li data-bs-toggle='tooltip' data-bs-placement='top' aria-label='View'>
                                          <a href='/admin/demande/$id' class='btn btn-sm btn-soft-primary'><i class='mdi mdi-eye-outline'></i></a>
                                      </li>
                                      <li data-bs-toggle='tooltip' data-bs-placement='top' aria-label='Edit'>
                                         <a href='/admin/demande/$id/edit' class='btn btn-sm btn-soft-success'><i class='mdi mdi-pencil-outline'></i></a>
                                      </li>
                                      <li data-bs-toggle='tooltip' data-bs-placement='top' aria-label='Supprimer'>
                                         <a href='/admin/demande/$id/supprimer' class='btn btn-sm btn-soft-danger'><i class='mdi mdi-delete-alert-outline'></i></a>
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

    #[Route('/{id}', name: 'admin_demande_show', methods: ['GET'])]
    public function show(Macaron $demande): Response
    {
        return $this->render('admin/demande/show.html.twig', [
            'demande' => $demande,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_demande_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Demande $demande, DemandeService $demandeService): Response
    {
        date_default_timezone_set("Africa/Abidjan");
        $form = $this->createForm(DemandeType::class, $demande);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if($form->get('photo')->getData())            $demande->setPhoto($form->get('photo')->getData());
            if($form->get('photoPieceFront')->getData())  $demande->setPhotoPieceFront($form->get('photoPieceFront')->getData());
            if($form->get('photoPieceBack')->getData())   $demande->setPhotoPieceBack($form->get('photoPieceBack')->getData());
            if($form->get('photoPermisFront')->getData()) $demande->setPhotoPermisFront($form->get('photoPermisFront')->getData());
            if($form->get('photoPermisBack')->getData())  $demande->setPhotoPermisBack($form->get('photoPermisBack')->getData());

            $demande->setStatus( $demande->getStatus());
            $demande->setReference( $demande->getCompany());
            $demande->setMontant( $demande->getTitre());
            $demandeService->storeDemande($demande);

            return $this->redirectToRoute('admin_demande_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/demande/edit.html.twig', [
            'demande' => $demande,
            'form' => $form,
        ]);
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
