<?php

namespace App\Controller\Admin;

use App\Entity\Payment;
use App\Form\PaymentType;
use App\Helper\DataTableHelper;
use App\Repository\PaymentRepository;
use App\Service\Payment\PaymentService;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('admin/payment')]
class PaymentController extends AbstractController
{

    #[Route('', name: 'admin_payment_index', methods: ['GET'])]
    public function index(Request $request, PaymentRepository $paymentRepository): Response
    {
        return $this->render('admin/payment/index.html.twig');
    }

    #[Route('/new', name: 'admin_payment_new', methods: ['GET', 'POST'])]
    public function new(Request $request, PaymentService $paymentService): Response
    {
        $payment = new Payment();
        $form = $this->createForm(PaymentType::class, $payment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            $data = $request->request->all();
            $paymentService->createPayment($payment);
            return $this->redirectToRoute('admin_payment_index');

        }
        return $this->renderForm('admin/payment/new.html.twig', [
            'payment' => $payment,
            'form' => $form,
        ]);
    }

    #[Route('/payment/dt', name: 'admin_payment_dt', methods: ['GET'])]
    public function datatable(Request $request, Connection $connection, PaymentRepository $paymentRepository)
    {
        date_default_timezone_set("Africa/Abidjan");
        $params = $request->query->all();
        $paramDB = $connection->getParams();
        $table = 'payment';
        $primaryKey = 'id';
        $payment = null;
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
                'dt' => 'reference',
            ],
            [
                'db' => 'montant',
                'dt' => 'montant',
            ],
            [
                'db' => 'status',
                'dt' => 'status'
            ],
            [
                'db' => 'operateur',
                'dt' => 'operateur'
            ],
            [
                'db' => 'code_payment_operateur',
                'dt' => 'code_payment_operateur'
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
                                          <a href='/admin/payment/$id' class='btn btn-sm btn-soft-primary'><i class='mdi mdi-eye-outline'></i></a>
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
        if(!empty($params['reference'])) {
            $whereResult .= " reference LIKE '%". $params['reference']. "%' AND";
        }
        if(!empty($params['code_payment_operateur'])) {
            $whereResult .= " code_payment_operateur LIKE '%". $params['code_payment_operateur']. "%' AND";
        }
        if(!empty($params['receipt_number'])){
            $whereResult .= " receipt_number LIKE '%". $params['receipt_number'] . "%' AND";
        }
        if(!empty($params['operateur'])) {
            $whereResult .= " operateur LIKE '%". $params['operateur']. "%' AND";
        }
        if(!empty($params['status'])) {
            $whereResult .= " status LIKE '%". $params['status']. "%' AND";
        }

        $whereResult = substr_replace($whereResult,'',-strlen(' AND'));
        $response = DataTableHelper::complex( $_GET, $sql_details, $table, $primaryKey, $columns, $whereResult);

        return new JsonResponse($response);
    }

    #[Route('/{id}', name: 'admin_payment_show', methods: ['GET'])]
    public function show(Payment $payment): Response
    {
        return $this->render('admin/payment/show.html.twig', [
            'payment' => $payment,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_payment_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Payment $payment, PaymentService $paymentService): Response
    {
        date_default_timezone_set("Africa/Abidjan");
        $form = $this->createForm(PaymentType::class, $payment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $paymentService->store($payment);
            return $this->redirectToRoute('admin_payment_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/payment/edit.html.twig', [
            'payment' => $payment,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/supprimer', name: 'admin_payment_delete', methods: ['GET','POST'])]
    public function delete(Request $request, Payment $payment, PaymentRepository $paymentRepository): Response
    {
        if ( true /* $this->isCsrfTokenValid('delete'.$payment->getId(), $request->request->get('_token')) */ ) {
            $fileName = "/var/www/html/public/frontend/media/" . $payment->getReceiptNumber() . "_receipt.pdf";
            if(file_exists($fileName)) {
                $fs =  new Filesystem();
                $fs->remove($fileName);
            }
            $paymentRepository->remove($payment, true);
        }
        return $this->redirectToRoute('admin_payment_index', [], Response::HTTP_SEE_OTHER);
    }

}
