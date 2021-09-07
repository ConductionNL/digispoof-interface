<?php

// src/Controller/DashboardController.php

namespace App\Controller;

use App\Service\DigiDMockService;
use App\Service\DigispoofService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class DeveloperController.
 *
 * @Route("/")
 */
class DefaultController extends AbstractController
{
    /**
     * @Route("/", methods={"GET"})
     * @Template
     *
     * @param Request          $request
     * @param DigiDMockService $digiDMockService
     * @param DigispoofService $digispoofService
     *
     * @return array
     */
    public function indexAction(Request $request, DigiDMockService $digiDMockService, DigispoofService $digispoofService)
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

        if ($request->query->has('SAMLRequest')) {
            $saml = $digiDMockService->handle($request);
            $people = $digispoofService->testSet();

            return ['people' => $people, 'type' => 'saml', 'saml' => $saml];
        }

        if ($type) {
            switch ($type) {
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

    /**
     * @Route("/", methods={"POST"})
     */
    public function redirectAction(Request $request, DigiDMockService $digiDMockService)
    {
        $result = $request->request->all();
        $artifact = $digiDMockService->saveBsnToCache($result['bsn']);

        return $this->redirect($result['endpoint']."?SAMLart=${artifact}");
    }

    /**
     * @Route("/artifact", methods={"POST"})
     */
    public function artifactAction(Request $request, DigiDMockService $digiDMockService)
    {
        if ($request->getContentType() !== 'xml') {
            throw new HttpException('500', 'Content is not of type: XML');
        }
        $xml = $digiDMockService->handleArtifact($request->getContent());

        $response = new Response($xml);

        $response->headers->set('Content-Type', 'xml');

        return $response;
    }
}
