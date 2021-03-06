<?php

// src/Controller/DashboardController.php

namespace App\Controller;

use App\Service\DigispoofService;
use Conduction\CommonGroundBundle\Service\CommonGroundService;
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

        if ($request->isMethod('POST') && $request->getContentType() == 'xml') {
            $saml = $digispoofService->handlePostBinding($request->getContent());
            $people = $digispoofService->testSet();
            return ['people' => $people, 'type' => 'saml', 'saml' => $saml];
        }

        if ($request->query->has('SAMLRequest')) {
            $saml = $digispoofService->handleRedirectBinding($request->query->get('SAMLRequest'));
            $people = $digispoofService->testSet();
            return ['people' => $people, 'type' => 'saml', 'saml' => $saml];
        }

        if ($request->isMethod('POST')) {
            $result = $request->request->all();
            $artifact = $digispoofService->saveBsnToCache($result['bsn']);
            return $this->redirect($result['endpoint'] . "?SAMLArt=${artifact}");
        }

        if ($type) {
            switch ($type) {
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

    /**
     * @Route("/artifact", methods={"POST"})
     */
    public function artifactAction(Request $request, DigispoofService $digispoofService)
    {

        if ($request->getContentType() !== 'xml') {
            throw new HttpException('500', 'Content is not of type: XML');
        }
        $xml = $digispoofService->handleArtifact($request->getContent());

        $response = new Response($xml);

        $response->headers->set('Content-Type', 'xml');

        return $response;
    }

}
