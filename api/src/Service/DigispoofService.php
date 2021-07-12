<?php


namespace App\Service;


use Conduction\CommonGroundBundle\Service\CommonGroundService;
use DateTime;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
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
     * this function retrieves people from brp endpoint
     *
     * @return mixed people retrieved from brp endpoint
     */
    public function getFromBRP()
    {
        return $this->commonGroundService->getResourceList(['component'=>'brp', 'type'=>'ingeschrevenpersonen'])['hydra:member'];
    }

    /**
     * This function generates a test data set with test people from vrijBRP
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

    public function saveBsnToCache($bsn): string
    {
        $uuid = Uuid::uuid4();
        $hash = md5($uuid->toString());
        $item = $this->cache->getItem('code_'. $hash);
        $item->set($bsn);


        $this->cache->save($item);
        var_dump($item);
        return $hash;
    }

    public function xmlStringToArray(string $xml): array
    {
        $xml = simplexml_load_string($xml);
        $attributes = (array)$xml->attributes();
        $attributes = $attributes['@attributes'];
        $issuer = (array)$xml->children('urn:oasis:names:tc:SAML:2.0:assertion');
        $saml = [
            'issuer' => $issuer['Issuer'],
            'assertionConsumerService' => $attributes['AssertionConsumerServiceURL'] ?? $attributes['AssertionConsumerServiceIndex'],
            'providerName' => $attributes['ProviderName'] ?? null
        ];

        if (filter_var($saml['assertionConsumerService'], FILTER_VALIDATE_URL)) {
            $saml['endpoint'] = $saml['assertionConsumerService'];
        } else {
            //handle Assertion
        }
        return $saml;
    }

    public function handleArtifact(string $xml)
    {
        $xml = simplexml_load_string($xml);
        $array = (array)$xml->children('http://schemas.xmlsoap.org/soap/envelope/')->children('urn:oasis:names:tc:SAML:2.0:protocol')->children('urn:oasis:names:tc:SAML:2.0:assertion');

        if (!isset($array['Artifact'])) {
            throw new HttpException('404', 'Artifact not found');
        }

        $bsn = $this->retrieveFromCache($array['Artifact']);

        return $this->buildArtifactResponse($bsn, $array['Artifact']);
    }

    public function buildArtifactResponse($bsn, $artifact)
    {
        $uuid = Uuid::uuid4();
        $artifact = preg_replace("/\s+/", "", $artifact);

        $message = [
            '@xmlns:SOAP-ENV' => 'http://schemas.xmlsoap.org/soap/envelope/',
            'SOAP-ENV:Body' => [
                'samlp:ArtifactResponse' => [
                    '@xmlns:samlp' => "urn:oasis:names:tc:SAML:2.0:protocol",
                    '@xmlns:saml' => "urn:oasis:names:tc:SAML:2.0:assertion",
                    '@xmlns:ds' => "http://www.w3.org/2000/09/xmldsig#",
                    '@xmlns:ec' => "http://www.w3.org/2001/10/xml-exc-c14n#",
                    '@ID' => $uuid->toString(),
                    '@Version' => "2.0",
                    '@IssueInstant' => date('Y-m-d H:i:s'),
                    '@InResponseTo' => $artifact,
                    'saml:Issuer' => "https://digipoof.demodam.nl",
                    'samlp:Status' => [
                        'samlp:StatusCode' => [
                            '@Value' => "urn:oasis:names:tc:SAML:2.0:status:Success"
                        ]
                    ],
                    'samlp:Response' => [
                        '@InResponseTo' => $artifact,
                        '@Version' => "2.0",
                        '@ID' => $uuid->toString(),
                        '@IssueInstant' => date('Y-m-d H:i:s'),
                        'saml:Issuer' => "https://digipoof.demodam.nl",
                        'saml:Subject' => [
                            'saml:NameID' => "s00000000:". $bsn,
                            'saml:SubjectConfirmation' => [
                                '@Method' => "urn:oasis:names:tc:SAML:2.0:cm:bearer",
                                'saml:SubjectConfirmationData' => [
                                    '@InResponseTo' => $artifact,
                                    '@Recipient' => 'https://digispoof.demodam.nl/artifact',
                                    '@NotOnOrAfter' => date("Y-m-d H:i:s",strtotime(date("Y-m-d H:i:s")." +2 minutes"))
                                ]
                            ]
                        ],
                        'saml:Condictions' => [
                            '@NotBefore' => date("Y-m-d H:i:s",strtotime(date("Y-m-d H:i:s")." -2 minutes")),
                            '@NotOnOrAfter' => date("Y-m-d H:i:s",strtotime(date("Y-m-d H:i:s")." +2 minutes")),
                            'saml:AudienceRestriction' => [
                                'saml:Audience' => $artifact,
                            ]
                        ],
                    ]
                ]
            ]
        ];

        return $this->xmlEncoder->encode($message, 'xml', ['remove_empty_tags' => true]);
    }

    public function retrieveFromCache($artifact): string
    {

        $item = $this->cache->getItem('code_'.preg_replace("/\s+/", "", $artifact));
        if (!$item->isHit()) {
            throw new HttpException('404', 'Artifact not found');
        }

        return $item->get();
    }
}
