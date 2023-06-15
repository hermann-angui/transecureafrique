<?php

namespace App\Controller\Client;

use App\Entity\Demande;
use App\Entity\OtpCode;
use App\Entity\Payment;
use App\Entity\User;
use App\Form\UserFormType;
use App\Helper\ImageGenerator;
use App\Repository\DemandeRepository;
use App\Repository\OtpCodeRepository;
use App\Service\Demande\DemandeService;
use App\Service\InfoBip\InfoBipService;
use App\Service\Otp\OtpService;
use App\Service\Wave\WaveService;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Knp\Snappy\Pdf;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Date;

#[Route('/payment')]
class PaymentController extends AbstractController
{
    #[Route(path: '/do', name: 'do_payment')]
    public function doPayment(Payment $paiement, WaveService $waveService): Response
    {
        $response = $waveService->makePayment($paiement);
        if($response) return $this->redirect($response->getWaveLaunchUrl());
        else return $this->redirectToRoute('home');
    }

    #[Route(path: '/wave', name: 'wave_payment_checkout_webhook')]
    public function callbackWavePayment(Request $request): Response
    {
        $payload =  json_decode($request->getContent(), true);
        return $this->json($payload);
    }

    #[Route(path: '/wave/checkout/{status}', name: 'wave_payment_callback')]
    public function wavePaymentCheckoutStatusCallback($status, Request $request): Response
    {
        return $this->render('frontend/subscription_payment/checkout_result.html.twig', [
            'status' => $status]);
    }


}
