<?php

namespace App\Controller\Client;

use App\Dtos\OtpRequest;
use App\Repository\DemandeRepository;
use App\Repository\OtpCodeRepository;
use App\Service\Otp\OtpService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class WebSiteController extends AbstractController
{
    private const CIV_CODE = "(+225)";

    #[Route(path: '/', name: 'home')]
    public function home(Request $request): Response
    {
        return $this->render('frontend/bs/index.html.twig');
    }

    #[Route(path: '/check/{macaron_qr_code_number}', name: 'check_macaron')]
    public function checkMacaron($macaron_qr_code_number,
                                 Request $request,
                                 DemandeRepository $demandeRepository): Response
    {
        $demande = $demandeRepository->findOneBy(["macaron_qrcode_number" => $macaron_qr_code_number]);
        if($demande?->getMacaron()){
            $valid = "valid";
            return $this->render("admin/macaron/check.html.twig", ["valid" => $valid]);
        }
        return $this->render("admin/macaron/check.html.twig");
    }

    #[Route(path: '/test', name: 'test')]
    public function test(Request $request, DemandeRepository $demandeRepository): Response
    {
        $total = $demandeRepository->findTotalDemandePayed();
        return $this->json([]);
    }

    #[Route(path: '/verify/receipt/{reference}', name: 'check_receipt')]
    public function checkReceipt($reference, DemandeRepository $demandeRepository, Request $request): Response
    {
        $demande = $demandeRepository->findOneBy(['reference' => $reference]);
        if($demande?->getPayment())  return $this->render('frontend/bs/receipt-check.html.twig', ["payment" => $demande->getPayment()]);
        return new Response("<strong>ATTENTION!! Ce recu n'est pas authentitique</strong>");
    }

    #[Route(path: '/auth', name: 'auth')]
    public function login(Request $request): Response
    {
        return $this->render('frontend/bs/auth.html.twig');
    }

    #[Route(path: '/otp/verify', name: 'check_otp', methods: ['GET', 'POST'])]
    public function checkOtp(Request $request, OtpCodeRepository $otpCodeRepository): Response
    {
        if ($request->getMethod() === "GET") return $this->redirectToRoute("auth");
        $otpCode = null;
        $code1 = $request->request->get('otpcode');
        $param = $request->request->all();
        if(array_key_exists("tt", $param)) $code2= implode($param["tt"]);
        if(!empty($code1)) $otpCode = $otpCodeRepository->findOneBy(['code' => $code1]);
        elseif(!empty($code2)) $otpCode = $otpCodeRepository->findOneBy(['code' => $code2]);
        if ($otpCode)  return $this->redirectToRoute('demande_select_type', ['authid' => $otpCode->getId()]);
        else  return $this->render('frontend/bs/otp.html.twig', ["error" => "Le Code " . ($code1 ?: $code2) . " est  incorrect. Veuillez entrer le code reçu par SMS ", "otp" => null]);
    }

    #[Route(path: '/otp', name: 'otp')]
    public function otp(Request $request, OtpService $otpService /*, InfoBipService $infoBipService*/): Response
    {
        $phoneNumber = $request->get('numerotelInput');
        if(!$phoneNumber) return $this->redirectToRoute('auth');
        $phoneNumber = trim(str_replace("-", "", substr($phoneNumber, strlen(self::CIV_CODE))));
        if (!$phoneNumber) return $this->redirectToRoute('auth');
        $otpCode = $otpService->getByPhone($phoneNumber);
        if (!$otpCode){
            $generatedCode = OtpService::generate(6);
            $otpCode = $otpService->create(new OtpRequest($generatedCode, $phoneNumber, null));
        }
        return $this->redirectToRoute('demande_select_type', ['authid' => $otpCode->getId()]);

        /*
        $existingOtp = $otpService->getByPhone($phoneNumber);
        // $otpService->checkOtpValidity($existingOtp);
        if (!$existingOtp){
            $generatedCode = OtpService::generate(6);
            $message = "Votre code de vérification transecureafrica.com : " . $generatedCode;
            $result = $infoBipService->sendMessageTo($message, $phoneNumber);
            if (!in_array($result["status"], ["REJECTED", "FAILED", "ERROR", "EXPIRED"])) {
                $otpService->create(new OtpRequest($generatedCode, $phoneNumber, $result["messageId"]));
            }
        }
        return $this->render('frontend/bs/otp.html.twig', ["otp" => $existingOtp]);
        */
    }
}
