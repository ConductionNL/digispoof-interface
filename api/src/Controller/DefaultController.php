<?php

// src/Controller/DashboardController.php

namespace App\Controller;

use App\Service\ApplicationService;
use App\Service\DigiDMockService;
use App\Service\DigispoofService;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\HeaderUtils;
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
        $backUrl = $request->query->get('backUrl');
        $type = $request->query->get('type');

        //responce is deprecated but still used in some applications so we still support it.
        $responseUrl = $request->query->get('responseUrl', $request->query->get('responceUrl'));

        if ($request->query->has('SAMLRequest')) {
            $saml = $digiDMockService->handle($request);
            $people = $digispoofService->testSet();

            return ['people' => $people, 'type' => 'saml', 'saml' => $saml, 'currentPath' => $this->generateUrl('app_default_index')];
        }

        switch ($type) {
            case 'brp':
                $people = $digispoofService->getFromBRP();
                break;
            default:
                $people = $digispoofService->testSet();
                break;
        }

        return ['people'=>$people, 'responseUrl' => $responseUrl, 'backUrl' => $backUrl, 'token' => $token, 'currentPath' => $this->generateUrl('app_default_index')];
    }

    /**
     * @Route("/", methods={"POST"})
     */
    public function redirectAction(Request $request, DigiDMockService $digiDMockService)
    {
        $result = $request->request->all();
        $artifact = $digiDMockService->saveBsnToCache($result['bsn']);

        return $this->redirect($result['endpoint']."?SAMLart=${artifact}");

        //        if ($request->isMethod('POST') && $request->getContentType() == 'xml') {
//            $saml = $digispoofService->handlePostBinding($request->getContent());
//            $people = $digispoofService->testSet();
//            return ['people' => $people, 'type' => 'saml', 'saml' => $saml];
//        }
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

    /**
     * @Route("/application", methods={"GET"})
     * @Template
     */
    public function applicationAction()
    {
        return ['currentPath' => $this->generateUrl('app_default_application')];
    }

    /**
     * @Route("/application/{id}/private_key", methods={"GET"})
     * @Template
     *
     * @param string             $id
     * @param ApplicationService $applicationService
     */
    public function applicationPrivateKeyAction(string $id, ApplicationService $applicationService)
    {
        $application = $applicationService->findApplicationById($id);

        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            'digispoof.key'
        );

        return new Response(
            $application->getPrivateKey(),
            200,
            [
                'Content-Type'        => 'application/x-pem-file',
                'Content-Disposition' => $disposition,
            ]
        );
    }

    /**
     * @Route("/application/{id}/certificate", methods={"GET"})
     *
     * @param string             $id
     * @param ApplicationService $applicationService
     */
    public function applicationCertificateAction(string $id, ApplicationService $applicationService)
    {
        $application = $applicationService->findApplicationById($id);

        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            'digispoof.crt'
        );

        return new Response(
            $application->getCertificate(),
            200,
            [
                'Content-Type'        => 'application/x-pem-file',
                'Content-Disposition' => $disposition,
            ]
        );
    }

    /**
     * @Route("/application/{id}", methods={"GET"})
     * @Template
     *
     * @param string             $id
     * @param ApplicationService $applicationService
     */
    public function applicationItemAction(Request $request, string $id, ApplicationService $applicationService)
    {
        return ['application' => $applicationService->findApplicationById($id), 'currentPath' => $this->generateUrl('app_default_application')];
    }

    /**
     * @Route("/application", methods={"POST"})
     *
     * @param Request            $request
     * @param ApplicationService $applicationService
     */
    public function applicationCreateAction(Request $request, ApplicationService $applicationService)
    {
        $requestData = $request->request->all();

        try {
            $application = $applicationService->createApplication($requestData);

            return $this->redirect($this->generateUrl('app_default_applicationitem', ['id' => $application->getId()]));
        } catch (Exception $exception) {
            $this->addFlash('error', $exception->getMessage());

            return $this->redirect($this->generateUrl('app_default_application'));
        }
    }
}
