<?php

namespace App\Controller\Admin;

use App\Entity\Macaron;
use App\Helper\FileUploadHelper;
use App\Repository\DemandeRepository;
use App\Repository\MacaronRepository;
use App\Repository\PaymentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin')]
class PageController extends AbstractController
{
    #[Route(path: '', name: 'admin_index')]
    public function index(Request $request,
                          PaymentRepository $paymentRepository,
                          MacaronRepository $macaronRepository): Response
    {
        if(!in_array("USER_DISTRICT", $this->getUser()->getRoles())){
            return $this->redirectToRoute('admin_district_index');
        }
        if(!in_array("USER_SUPER_ADMIN", $this->getUser()->getRoles())){
            return $this->redirectToRoute('admin_demande_index');
        }
        $stats = [
            "macarons" => $macaronRepository->count([]),
            "demandes" => $paymentRepository->count([]),
            "daily" => $paymentRepository->findTotalDaily(),
            "weekly" => $paymentRepository->findTotalWeekly(),
        ];
        return $this->render('admin/pages/index.html.twig', ["stats"=> $stats]);
    }

    #[Route('/upload/carte_grise', name: 'admin_upload_carte_grise', methods: ['GET', 'POST'])]
    public function upload(Macaron $macaron, FileUploadHelper $fileUploadHelper): Response
    {
        date_default_timezone_set("Africa/Abidjan");
        set_time_limit(0);
        /* @var UploadedFile $file */
        if(!empty($file = $request->files->get('file'))){
            $mime = $file->getMimeType();
            $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/';
            if(in_array($mime, ['image/png','image/jpeg','image/jpg','image/gif','text/csv','text/plain'])){
                $fileUploadHelper->upload($file, $uploadDir,true);
            }
        }
        return $this->renderForm('admin/member/upload.html.twig');
    }

}
