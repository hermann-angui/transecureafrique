<?php

namespace App\Controller\Admin;

use App\Service\Demande\DemandeService;
use App\Service\Payment\PaymentService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('api/stats')]
class ApiStatsController extends AbstractController
{

    #[Route('/district', name: 'api_stats_district', methods: ['GET'])]
    public function index(Request $request,
                          PaymentService $paymentService,
                          DemandeService $demandeService): Response
    {
        $delivered = [
            "Jan" => 0,
            "Fev" => 0,
            "Mars" => 0,
            "Avr" => 0,
            "Mai" => 0,
            "Juin" => 0,
            "Juillet" => 0,
            "Aout" => 0,
            "Sep" => 0,
            "Oct" => 0,
            "Nov" => 0,
            "Dec" => 0,
        ];
        $statsEachMonth = $paymentService->getTotalEachMonth();
        $keys = array_keys($delivered);
        foreach($statsEachMonth as $stat){
            $index = $stat['mois_num'] - 1;
            $delivered[$keys[$index]] = $stat['total'];
        }

        $undelivered = [
            "Jan" => 0,
            "Fev" => 0,
            "Mars" => 0,
            "Avr" => 0,
            "Mai" => 0,
            "Juin" => 0,
            "Juillet" => 0,
            "Aout" => 0,
            "Sep" => 0,
            "Oct" => 0,
            "Nov" => 0,
            "Dec" => 0,
        ];
        $statsEachMonth = $demandeService->getTotalUndeliveredEachMonth();
        $keys = array_keys($undelivered);
        foreach($statsEachMonth as $stat){
            $index = $stat['mois_num'] - 1;
            $undelivered[$keys[$index]] = $stat['total'];
        }

        $stats['monthly'] = [
            "axis" => array_keys($delivered),
            "delivres" => ["name"=> "Délivrés", "type" => "bar", "color" => '#339966', "data" => array_values($delivered)],
            "demande" =>  ["name"=> "Demandés", "type" => "bar", "color" => '#ff751a', "data" => array_values($undelivered)]
        ];

        $group_by_energie = $demandeService->getGroupByEnergie();
        $group_by_marque = $demandeService->getGroupByMarque();

        $stats["energie"] = [
            "legend" => array_column( $group_by_energie, "name"),
            "series" => $group_by_energie
        ];
        $stats["marque"] = [
            "legend" => array_column( $group_by_marque, "name"),
            "series" => $group_by_marque
        ];

        $presentation =  json_encode($stats, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        $response = new Response($presentation);
        $response->headers->set('Content-Type', 'application/json');

        return $response;

    }


}
