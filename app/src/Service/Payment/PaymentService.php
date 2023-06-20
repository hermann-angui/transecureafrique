<?php

namespace App\Service\Payment;

use App\Entity\Payment;
use App\Helper\PdfGenerator;
use App\Repository\PaymentRepository;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;

class PaymentService
{
    private const WEBSITE_URL = "https://transecureafrica.com";
    private const MEDIA_DIR = "/var/www/html/public/frontend/media/";
    private const MONTANT = 100;

    public function __construct(
        private PdfGenerator     $pdfGenerator,
        private PaymentRepository $paymentRepository)
    {
    }

    /**
     * @param Payment|null $payment
     * @param string $viewTemplate
     * @return PdfResponse
     */
    public function downloadPdfReceipt(?Payment $payment, string $viewTemplate){
        $content = $this->generateReceipt($payment,$viewTemplate);
        return new PdfResponse($content, 'recu_macaron.pdf');
    }


    /**
     * @param Payment|null $payment
     * @param string $viewTemplate
     * @return string|null
     */
    public function generateReceipt(?Payment $payment, string $viewTemplate)
    {
        try{
            $demande = $payment->getDemande();
            $numeroChassis = $demande->getNumeroVinChassis();
            $qrCodeData = self::WEBSITE_URL . "/check/receipt/" . $numeroChassis;
            $content = $this->pdfGenerator->generateBarCode($qrCodeData, 50, 50);
            $folder = self::MEDIA_DIR . $numeroChassis;
            file_put_contents( $folder . "_barcode.png", $content);
            $content = $this->pdfGenerator->generatePdf($viewTemplate, ['payment' => $payment, 'demande' => $payment->getDemande()]);
            file_put_contents($folder . "_receipt.pdf", $content);
            if(file_exists($folder . "_barcode.png")) \unlink($folder . "_barcode.png");
            return $content ?? null;
        }catch(\Exception $e){
            if(file_exists($folder . "_barcode.png")) \unlink($folder . "_barcode.png");
            if(file_exists($folder . "_receipt.pdf")) \unlink($folder . "_receipt.pdf");
            return null;
        }

    }

    /**
     * @param Payment $payment
     * @return void
     */
    public function save(Payment $payment): void
    {
         $this->paymentRepository->add($payment, true);
    }


    /**
     * @param array $data
     * @return Payment
     * @throws \Exception
     */
    public function create(array $data): Payment {
        $payment = new Payment();
        $payment->setMontant($data["montant"]);
        $payment->setOperateur("WAVE");
        $payment->setType(strtoupper($data["type"]));
        $payment->setDemande($data["demande"]);
        $this->paymentRepository->add($payment, true);
        return $payment;
    }

    /**
     * @return int
     */
    public function getTotalDemandes(){
        return $this->paymentRepository->count([]);
    }

    /**
     * @return float|int|mixed|string
     */
    public function getTotalDailyDemande(){
        return $this->paymentRepository->findTotalDaily();
    }

    /**
     * @return float|int|mixed|string
     */
    public function getTotalWeeklyDemandes(){
       return $this->paymentRepository->findTotalWeekly();
    }

    public function  getTotalEachMonth(){
        return $this->paymentRepository->findTotalEachMonth();
    }
}