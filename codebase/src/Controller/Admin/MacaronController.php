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
        return $this->render('admin/macaron/index.html.twig');
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
            $macaron->setLastEditor($this->getUser());
            $macaron->setReference($demande->getReference());
            $macaron->setMacaronQrcodeNumber($demande->getMacaronQrcodeNumber());
            $macaron->setStatus("COMPLETED");
            $macaron->setDemande($demande);
            $macaron->setNumeroTelephoneProprietaire($demande->getNumeroTelephoneProprietaire());
            $macaron->setValidityTo(new \DateTime());
            $macaron->setValidityFrom(new \DateTime('last day of December this year'));
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
                'db' => 'macaron_qrcode_number',
                'dt' => 'macaron_qrcode_number',
            ],
            [
                'db' => 'numero_recepisse',
                'dt' => 'numero_recepisse',
            ],
            [
                'db' => 'reference',
                'dt' => 'reference'
            ],
            [
                'db' => 'status',
                'dt' => 'status'
            ],
            [
                'db' => 'numero_telephone_proprietaire',
                'dt' => 'numero_telephone_proprietaire'
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
                'dt'        => 'id',
                'formatter' => function($d, $row) {
                    $id = $row['id'];
                    $content =  "<ul class='list-unstyled hstack gap-1 mb-0'>
                                      <li data-bs-toggle='tooltip' data-bs-placement='top' aria-label='View'>
                                          <a href='/admin/macaron/$id' class='btn btn-sm btn-soft-primary'><i class='mdi mdi-eye-outline'></i></a>
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
