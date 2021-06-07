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

        //responce is deprecated but still used in some applications so we still support it.
        if ($request->query->get('responceUrl')) {
            $responseUrl = $request->query->get('responceUrl');
        } else {
            $responseUrl = $request->query->get('responseUrl');
        }

        $backUrl = $request->query->get('backUrl');
        $brpUrl = $request->query->get('brpUrl');
        $url = $request->getHost();

        if ($brpUrl && $brpUrl == 'localhost') {
            $people = [
//                [
//                    'burgerservicenummer'   => '900220806',
//                    'naam' => [
//                        'voornamen'             => 'testpersoon',
//                        'geslachtsnaam'         => '900220806',
//                        ],
//                ],                [
                    'burgerservicenummer'   => '900220818',
                    'naam' => [
                        'voornamen'             => 'testpersoon',
                        'geslachtsnaam'         => '900220818',
                    ],
                ],                [
                    'burgerservicenummer'   => '900220831',
                    'naam' => [
                        'voornamen'             => 'testpersoon',
                        'geslachtsnaam'         => '900220831',
                    ],
                ],                [
                    'burgerservicenummer'   => '900220843',
                    'naam' => [
                        'voornamen'             => 'testpersoon',
                        'geslachtsnaam'         => '900220843',
                    ],
                ],                [
                    'burgerservicenummer'   => '900220855',
                    'naam' => [
                        'voornamen'             => 'testpersoon',
                        'geslachtsnaam'         => '900220855',
                    ],
                ],
            ];
        } elseif($brpUrl) {
            $people = $commonGroundService->getResourceList($brpUrl);
        } else {
            $people = $commonGroundService->getResourceList(['component'=>'brp', 'type'=>'ingeschrevenpersonen'])['hydra:member'];
        }

        return ['people'=>$people, 'responseUrl' => $responseUrl, 'backUrl' => $backUrl, 'token' => $token];
    }
}
