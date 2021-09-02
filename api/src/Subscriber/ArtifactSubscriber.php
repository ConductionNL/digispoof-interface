<?php
namespace App\Subscriber;

use App\Service\DigiDMockService;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Cache\Adapter\AdapterInterface as CacheInterface;
use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\Login;
use Conduction\CommonGroundBundle\Service\CommonGroundService;
use Conduction\IdVaultBundle\Event\IdVaultEvents;
use Conduction\IdVaultBundle\Event\LoggedInEvent;
use Conduction\IdVaultBundle\Event\NewUserEvent;
use Conduction\IdVaultBundle\Service\IdVaultService;
use GuzzleHttp\Client;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionBagInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

class ArtifactSubscriber implements EventSubscriberInterface
{
    private $commonGroundService;
    private DigiDMockService $digiDMockService;
    private $params;

    public function __construct(CommonGroundService $commonGroundService, DigiDMockService $digiDMockService, ParameterBagInterface $params)
    {
        $this->commonGroundService = $commonGroundService;
        $this->digiDMockService = $digiDMockService;
        $this->params = $params;
    }
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => ['artifact', EventPriorities::PRE_VALIDATE],
        ];
    }
    public function artifact(RequestEvent $event)
    {
        $route = $event->getRequest()->getPathInfo();
        $method = $event->getRequest()->getMethod();

        // Let limit the subscriber to a simple get request
        if($route != '/artifact' || $method != 'POST' ){
            return;
        }

        $components = $this->params->get('components');

        $loggingComponent = $this->getLoggingComponent($components);
        if (!$loggingComponent) {
            return;
        }

        $bsn = $this->digiDMockService->retrieveBsn($event->getRequest()->getContent());

        $verwerkteSoortenGegevens = [
            'soortGegeven' => 'BSN'
        ];
        $verwerkteSoortenGegevens = $this->commonGroundService->createResource($verwerkteSoortenGegevens, ['component' => $loggingComponent, 'type' => 'verwerkt_soort_gegevens']);

        $verwerkteObject = [
            'objecttype' => 'persoon',
            'soortObjectId' => 'BSN',
            'objectId' => $bsn,
            'verwerkteSoortGegevens' => [
                '/verwerkt_soort_gegevens/'. $verwerkteSoortenGegevens['id'],
            ]
        ];

        $verwerkteObject = $this->commonGroundService->createResource($verwerkteObject, ['component' => $loggingComponent, 'type' => 'verwerkt_objects']);

        $verwerkingsacties = [
            'actieNaam' => 'BSN nummer opgevraagd',
            'handelingsNaam' => 'Digispoof login',
            'verwerkingsnaam' => 'Artifact bevraging',
            'vertrouwelijkheid' => 'normaal',
            'tijdstip' => date('Y-m-d H:i:s'),
            'verwerkteObjecten' => [
                '/verwerkt_objects/' . $verwerkteObject['id'],
            ]
        ];

        $verwerkingsacties = $this->commonGroundService->createResource($verwerkingsacties, ['component' => $loggingComponent, 'type' => 'verwerkings_acties']);

    }

    public function getLoggingComponent(array $components): ?string
    {
        if (key_exists('loggingcomponent', $components)) {
            return 'loggingcomponent';
        } elseif (key_exists('logging-component', $components)) {
            return 'logging-component';
        } elseif (key_exists('loggingcomponent', $components)) {
            return 'loggingcomponent';
        } elseif (key_exists('lgc', $components)) {
            return 'lgc';
        } elseif (key_exists('logging', $components)) {
            return 'logging';
        } else {
            return null;
        }
    }

}
