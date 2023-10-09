<?php

namespace App\Service\Payment;

use App\Entity\Payment;
use App\Helper\PdfGenerator;
use App\Repository\PaymentRepository;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Symfony\Component\Uid\Uuid;

class PaymentService
{
    private const WEBSITE_URL = "https://transecureafrica.com";
    private const MEDIA_DIR = "/var/www/html/public/frontend/media/";
    private const MONTANT = 10100;

    public function __construct(
        private PdfGenerator     $pdfGenerator,
        private PaymentRepository $paymentRepository)
    {
    }

    public static function generateReference() {
        $now = new \DateTime();
        $year = $now->format("y");
        return $year . strtoupper(substr(Uuid::v4()->toRfc4122(), 0, 8));
    }

    /**
     * @param Payment|null $payment
     * @param string $viewTemplate
     * @return PdfResponse
     */
    public function downloadPdfReceipt(?Payment $payment){
        set_time_limit(0);
        $content = $this->generateReceipt($payment);
        return new PdfResponse($content, 'recu_macaron.pdf');
    }


    /**
     * @param Payment|null $payment
     * @param string $viewTemplate
     * @return string|null
     */
    public function generateReceipt(?Payment $payment)
    {
        try {
            $qrCodeData = self::WEBSITE_URL . "/verify/receipt/" . $payment->getReceiptNumber();
            $content = $this->pdfGenerator->generateBarCode($qrCodeData, 50, 50);
            $folder = self::MEDIA_DIR . $payment->getReceiptNumber();
            if(!file_exists(self::MEDIA_DIR)) mkdir(self::MEDIA_DIR, 0777, true);
            file_put_contents( $folder . "_barcode.png", $content);

            if($payment->getGroupe() && $payment->getGroupeId()){

            }else{
                $viewTemplate = 'frontend/bs/receipt-pdf.html.twig';
                $demandes = $payment->getDemandes();
                $content = $this->pdfGenerator->generatePdf($viewTemplate, ['payment' => $payment, 'demande' => $demandes[0]]);
                file_put_contents($folder . "_receipt.pdf", $content);
                if(file_exists($folder . "_barcode.png")) \unlink($folder . "_barcode.png");
                return $content ?? null;
            }

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
    public function store(Payment $payment): void
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
        $payment->setReceiptNumber($this->generateReference());
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

    public function  getTotalPaymentWithoutMacaron(){
        return $this->paymentRepository->findTotalWithoutMacaron();
    }
}
