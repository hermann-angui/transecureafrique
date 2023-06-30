<?php

namespace App\Controller\Client;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ErrorController extends AbstractController
{
    #[Route(path: '/error', name: 'error', methods: ['POST', 'GET'])]
    public function show(Request $request): Response
    {
        return $this->render("bundles/TwigBundle/Exception/error.html.twig", ["message" => $request->get("message")]);
    }
}
