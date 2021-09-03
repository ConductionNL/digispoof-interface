<?php

namespace App\Subscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Service\DigiDMockService;
use Conduction\CommonGroundBundle\Service\CommonGroundService;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ArtifactSubscriber implements EventSubscriberInterface
{
    private CommonGroundService $commonGroundService;
    private DigiDMockService $digiDMockService;
    private ParameterBagInterface $parameterBag;
    private string $loggingComponent;

    public function __construct(CommonGroundService $commonGroundService, DigiDMockService $digiDMockService, ParameterBagInterface $params)
    {
        $this->commonGroundService = $commonGroundService;
        $this->digiDMockService = $digiDMockService;
        $this->parameterBag = $params;
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
        if ($route != '/artifact' || $method != 'POST') {
            return;
        }
        $this->loggingComponent = $this->getLoggingComponent();
        if (!$this->loggingComponent) {
            return;
        }
        $bsn = $this->digiDMockService->retrieveBsn($event->getRequest()->getContent());
        $verwerkteSoortenGegevens = $this->createProcessedDataTypes();
        $verwerkteObject = $this->createProcessedObject($verwerkteSoortenGegevens, $bsn);
        $verwerkingsacties = $this->createProcessingActions($verwerkteObject);
    }

    public function createProcessedDataTypes(): array
    {
        $verwerkteSoortenGegevens = [
            'soortGegeven' => 'BSN',
        ];
        return $this->commonGroundService->createResource($verwerkteSoortenGegevens, ['component' => $this->loggingComponent, 'type' => 'verwerkt_soort_gegevens']);
    }

    public function createProcessedObject(array $verwerkteSoortenGegevens, string $bsn): array
    {
        $verwerkteObject = [
            'objecttype'             => 'persoon',
            'soortObjectId'          => 'BSN',
            'objectId'               => $bsn,
            'verwerkteSoortGegevens' => [
                '/verwerkt_soort_gegevens/'.$verwerkteSoortenGegevens['id'],
            ],
        ];

        return $this->commonGroundService->createResource($verwerkteObject, ['component' => $this->loggingComponent, 'type' => 'verwerkt_objects']);
    }

    public function createProcessingActions(array $verwerkteObject){
        $verwerkingsacties = [
            'actieNaam'         => 'BSN nummer opgevraagd',
            'handelingsNaam'    => 'Digispoof login',
            'verwerkingsnaam'   => 'Artifact bevraging',
            'vertrouwelijkheid' => 'normaal',
            'tijdstip'          => date('Y-m-d H:i:s'),
            'verwerkteObjecten' => [
                '/verwerkt_objects/'.$verwerkteObject['id'],
            ],
        ];

        return $this->commonGroundService->createResource($verwerkingsacties, ['component' => $this->loggingComponent, 'type' => 'verwerkings_acties']);
    }

    public function getLoggingComponent(): ?string
    {

        $components = $this->parameterBag->get('components');
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
