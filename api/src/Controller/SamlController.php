<?php

// src/Controller/DashboardController.php

namespace App\Controller;

use App\Service\DigiDMockService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class DeveloperController.
 *
 * @Route("/saml")
 */
class SamlController extends AbstractController
{
    /**
     * @Route("/metadata", methods={"GET"})
     */
    public function indexAction(DigiDMockService $digiDMockService)
    {
        return new Response(
            $digiDMockService->generateMetadataFile(),
            200,
            ['Content-Type' => 'application/xml']
        );
    }
}
