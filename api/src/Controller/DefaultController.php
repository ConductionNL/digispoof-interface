<?php

// src/Controller/DashboardController.php

namespace App\Controller;

use App\Service\DigispoofService;
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
    public function indexAction(Request $request, DigispoofService $digispoofService)
    {
        $token = $request->query->get('token');

        //responce is deprecated but still used in some applications so we still support it.
        if ($request->query->get('responceUrl')) {
            $responseUrl = $request->query->get('responceUrl');
        } else {
            $responseUrl = $request->query->get('responseUrl');
        }

        $backUrl = $request->query->get('backUrl');
        $type = $request->query->get('type');

        if ($type) {
            switch ($type) {
                case 'saml':
                    $people = $digispoofService->getFromBRP();
                    break;
                case 'testset':
                    $people = $digispoofService->testSet();
                    break;
                case 'brp':
                    $people = $digispoofService->getFromBRP();
                    break;
                default:
                    $people = $digispoofService->testSet();
                    break;
            }
        } else {
            $people = $digispoofService->testSet();
        }

        return ['people'=>$people, 'responseUrl' => $responseUrl, 'backUrl' => $backUrl, 'token' => $token];
    }

}
