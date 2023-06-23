<?php

namespace App\Controller\Client;

use App\Dtos\OtpRequest;
use App\Repository\DemandeRepository;
use App\Repository\OtpCodeRepository;
use App\Service\InfoBip\InfoBipService;
use App\Service\Otp\OtpService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class WebSiteController extends AbstractController
{
    #[Route(path: '/', name: 'home')]
    public function home(Request $request): Response
    {
        return $this->render('frontend/bs/index2.html.twig');
    }
    #[Route(path: '/check/receipt/{chassis}', name: 'check_receipt')]
    public function checkReceipt($chassis, DemandeRepository $demandeRepository, Request $request): Response
    {
        $demande = $demandeRepository->findOneBy(['numero_vin_chassis' => $chassis]);
        if($demande) {
            if($demande->getPayment()) return $this->render('frontend/bs/receipt-check.html.twig',["payment" => $demande->getPayment()]);
        }
        return new Response("<strong>ATTENTION!! Ce recu n'est pas authentitique</strong>");
    }

    #[Route(path: '/auth', name: 'auth')]
    public function login(Request $request): Response
    {
        return $this->render('frontend/bs/auth.html.twig');
    }

    #[Route(path: '/otp/check', name: 'check_otp', methods: ['GET', 'POST'])]
    public function checkOtp(Request $request, OtpCodeRepository $otpCodeRepository): Response
    {
        if ($request->getMethod() === "GET") return $this->redirectToRoute("auth");
        $otpCode = null;
        $code1 = $request->request->get('otpcode');
        $param = $request->request->all();
        if(array_key_exists("tt", $param)) $code2= implode($param["tt"]);
        if(!empty($code1)) $otpCode = $otpCodeRepository->findOneBy(['code' => $code1]);
        elseif(!empty($code2)) $otpCode = $otpCodeRepository->findOneBy(['code' => $code2]);
        if ($otpCode) {
            return $this->redirectToRoute('demande_select_type', ['authid' => $otpCode->getId()]);
        } else {
            return $this->render('frontend/bs/otp.html.twig', ["error" => "Le Code " . ($code1 ?: $code2) . " est  incorrect. Veuillez entrer le code reçu par SMS ", "otp" => null]);
        }
    }

    #[Route(path: '/otp', name: 'otp')]
    public function otp(Request $request, InfoBipService $infoBipService, OtpService $otpService): Response
    {
        $phoneNumber = $request->get('numerotelInput');
        if (!$phoneNumber) return $this->redirectToRoute('auth');
        $existingOtp = $otpService->getByPhone($phoneNumber);
       // $otpService->checkOtpValidity($existingOtp);
        if (!$existingOtp){
            $generatedCode = OtpService::generate(6);
            $message = "Votre code de vérification transecureafrica.com : " . $generatedCode;
            $result = $infoBipService->sendMessageTo($message, $phoneNumber);
            if (!in_array($result["status"], ["REJECTED", "FAILED", "ERROR", "EXPIRED"])) {
                $otpService->create(new OtpRequest(
                        $generatedCode,
                        $phoneNumber,
                        $result["messageId"])
                );
            }
        }

        return $this->render('frontend/bs/otp.html.twig', ["otp" => $existingOtp]);
    }


}
