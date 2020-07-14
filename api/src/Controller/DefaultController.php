<?php

// src/Controller/DashboardController.php

namespace App\Controller;

use Conduction\CommonGroundBundle\Service\CommonGroundService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class DeveloperController.
 *
 * @Route("/")
 */
class DefaultController extends AbstractController
{
    /**
     * @Route("/")
     * @Template
     */
    public function indexAction(Request $request, CommonGroundService $commonGroundService)
    {
        $token = $request->query->get('token');
        $responceUrl = $request->query->get('responceUrl');
        $brpUrl = $request->query->get('brpUrl');
        $url = $request->getHost();

        if ($brpUrl) {
            $people = $commonGroundService->getResourceList($brpUrl);
        } else {
            $people = $commonGroundService->getResourceList(['component'=>'brp', 'type'=>'ingeschrevenpersonen']);
        }

        return ['people'=>$people, 'responceUrl' => $responceUrl, 'token' => $token];
    }
}
