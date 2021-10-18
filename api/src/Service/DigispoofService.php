<?php

namespace App\Service;

use Conduction\CommonGroundBundle\Service\CommonGroundService;
use Symfony\Component\Cache\Adapter\AdapterInterface as CacheInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Serializer\Encoder\XmlEncoder;

class DigispoofService
{
    private CommonGroundService $commonGroundService;
    private CacheInterface $cache;
    private XmlEncoder $xmlEncoder;

    public function __construct(CommonGroundService $commonGroundService, CacheInterface $cache)
    {
        $this->commonGroundService = $commonGroundService;
        $this->cache = $cache;
        $this->xmlEncoder = new XmlEncoder(['xml_root_node_name' => 'SOAP-ENV:Envelope']);
    }

    /**
     * this function retrieves people from brp endpoint.
     *
     * @return mixed people retrieved from brp endpoint
     */
    public function getFromBRP()
    {
        return $this->commonGroundService->getResourceList(['component'=>'brp', 'type'=>'ingeschrevenpersonen'])['hydra:member'];
    }

    public function testSetHt(): array
    {
        return [
            [
                'burgerservicenummer'   => '999999102',
                'naam'                  => [
                    'voornamen'             => 'Jeroen',
                    'geslachtsnaam'         => 'Oranje',
                ],
            ],
            [
                'burgerservicenummer'   => '999999114',
                'naam'                  => [
                    'voornamen'             => 'Yvonne',
                    'geslachtsnaam'         => 'Rood',
                ],
            ],
            [
                'burgerservicenummer'   => '999999126',
                'naam'                  => [
                    'voornamen'             => 'Stan',
                    'geslachtsnaam'         => 'Oranje',
                ],
            ],
            [
                'burgerservicenummer'   => '999999138',
                'naam'                  => [
                    'voornamen'             => 'Tim',
                    'geslachtsnaam'         => 'Oranje',
                ],
            ],
            [
                'burgerservicenummer'   => '999999151',
                'naam'                  => [
                    'voornamen'             => 'Luuk',
                    'geslachtsnaam'         => 'Oranje',
                ],
            ],
            [
                'burgerservicenummer'   => '999999163',
                'naam'                  => [
                    'voornamen'             => 'Wim',
                    'geslachtsnaam'         => 'Fris',
                ],
            ],            [
                'burgerservicenummer'   => '999999011',
                'naam'                  => [
                    'voornamen'             => 'Angeline',
                    'geslachtsnaam'         => 'Warm',
                ],
            ],
            [
                'burgerservicenummer'   => '999999175',
                'naam'                  => [
                    'voornamen'             => 'Renate',
                    'geslachtsnaam'         => 'Fris',
                ],
            ],
            [
                'burgerservicenummer'   => '999999060',
                'naam'                  => [
                    'voornamen'             => 'Leon',
                    'geslachtsnaam'         => 'Giraf',
                ],
            ],
            [
                'burgerservicenummer'   => '999999187',
                'naam'                  => [
                    'voornamen'             => 'Tobias',
                    'geslachtsnaam'         => 'Reus',
                ],
            ],
            [
                'burgerservicenummer'   => '999999199',
                'naam'                  => [
                    'voornamen'             => 'Ton',
                    'geslachtsnaam'         => 'Reus',
                ],
            ],
            [
                'burgerservicenummer'   => '999999205',
                'naam'                  => [
                    'voornamen'             => 'Inge',
                    'geslachtsnaam'         => 'Links',
                ],
            ],            [
                'burgerservicenummer'   => '999999217',
                'naam'                  => [
                    'voornamen'             => 'Erik',
                    'geslachtsnaam'         => 'Rechts',
                ],
            ],
            [
                'burgerservicenummer'   => '999993653',
                'naam'                  => [
                    'voornamen'             => 'Suzanne',
                    'geslachtsnaam'         => 'van der Stappen',
                ],
            ],
            [
                'burgerservicenummer'   => '999990639',
                'naam'                  => [
                    'voornamen'             => 'Mattheus',
                    'geslachtsnaam'         => 'du Burck',
                ],
            ],
            [
                'burgerservicenummer'   => '999993872',
                'naam'                  => [
                    'voornamen'             => 'Peter-Jan',
                    'geslachtsnaam'         => 'van der Meijden',
                ],
            ],
            [
                'burgerservicenummer'   => '999992740',
                'naam'                  => [
                    'voornamen'             => 'Jael',
                    'geslachtsnaam'         => 'de Jager',
                ],
            ],
            [
                'burgerservicenummer'   => '999991723',
                'naam'                  => [
                    'voornamen'             => 'Lukas',
                    'geslachtsnaam'         => 'Klamer',
                ],
            ],
            [
                'burgerservicenummer'   => '999990317',
                'naam'                  => [
                    'voornamen'             => 'Nasier',
                    'geslachtsnaam'         => 'Boeddhoe',
                ],
            ],
            [
                'burgerservicenummer'   => '999993793',
                'naam'                  => [
                    'voornamen'             => 'Rodney',
                    'geslachtsnaam'         => 'Plieger',
                ],
            ],
            [
                'burgerservicenummer'   => '999990561',
                'naam'                  => [
                    'voornamen'             => 'Thomas',
                    'geslachtsnaam'         => 'Thuisloos',
                ],
            ],
            [
                'burgerservicenummer'   => '999998870',
                'naam'                  => [
                    'voornamen'             => 'Marjolein',
                    'geslachtsnaam'         => 'Thuisloos',
                ],
            ],
        ];
    }

    /**
     * This function generates a test data set with test people from vrijBRP.
     *
     * @return array[] returns test people array
     */
    public function testSet(): array
    {
        return [
            [
                'burgerservicenummer'   => '999997002',
                'naam'                  => [
                    'voornamen'             => 'Jasper Roeland',
                    'geslachtsnaam'         => 'Duijn',
                ],
            ],
            [
                'burgerservicenummer'   => '999990822',
                'naam'                  => [
                    'voornamen'             => 'Charles',
                    'geslachtsnaam'         => 'Kierkegaard',
                ],
            ],
            [
                'burgerservicenummer'   => '999994505',
                'naam'                  => [
                    'voornamen'             => 'Çigdem',
                    'geslachtsnaam'         => 'Kemal',
                ],
            ],
            [
                'burgerservicenummer'   => '999996344',
                'naam'                  => [
                    'voornamen'             => 'Marjolein Iris',
                    'geslachtsnaam'         => 'Nagelhout',
                ],
            ],
            [
                'burgerservicenummer'   => '999990226',
                'naam'                  => [
                    'voornamen'             => 'Jeannette',
                    'geslachtsnaam'         => 'Overvaart',
                ],
            ],
            [
                'burgerservicenummer'   => '999997622',
                'naam'                  => [
                    'voornamen'             => 'Danielle',
                    'geslachtsnaam'         => 'Nolles',
                ],
            ],
        ];
    }

    public function handleRedirectBinding(string $samlRequest): array
    {
        $xml = base64_decode($samlRequest);
        $xml = gzinflate($xml);

        return $this->handlePostBinding($xml);
    }

    public function handlePostBinding(string $xml): array
    {
        return $this->xmlStringToArray($xml);
    }

    public function xmlStringToArray(string $xml): array
    {
        $xml = simplexml_load_string($xml);
        $attributes = (array) $xml->attributes();
        $attributes = $attributes['@attributes'];
        $issuer = (array) $xml->children('urn:oasis:names:tc:SAML:2.0:assertion');
        $saml = [
            'issuer'                   => $issuer['Issuer'],
            'assertionConsumerService' => $attributes['AssertionConsumerServiceURL'] ?? $attributes['AssertionConsumerServiceIndex'],
            'providerName'             => $attributes['ProviderName'] ?? null,
        ];

        if (filter_var($saml['assertionConsumerService'], FILTER_VALIDATE_URL)) {
            $saml['endpoint'] = $saml['assertionConsumerService'];
        } else {
            //handle Assertion
        }

        return $saml;
    }

    public function retrieveFromCache($artifact): string
    {
        $item = $this->cache->getItem('code_'.preg_replace("/\s+/", '', $artifact));
        if (!$item->isHit()) {
            throw new HttpException('404', 'Artifact not found');
        }

        return $item->get();
    }
}
