<?php

namespace App\Service;

use App\Exception\DigiDException;
use OneLogin\Saml2\Utils;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Cache\Adapter\AdapterInterface as CacheInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Serializer\Encoder\XmlEncoder;

class DigiDMockService
{
    private XmlEncoder $xmlEncoder;
    private ParameterBagInterface $parameterBag;
    private FlashBagInterface $flashBag;
    private CacheInterface $cache;
    private ApplicationService $applicationService;

    public function __construct(ParameterBagInterface $parameterBag, CacheInterface $cache, FlashBagInterface $flashBag, ApplicationService $applicationService)
    {
        $this->xmlEncoder = new XmlEncoder([]);
        $this->parameterBag = $parameterBag;
        $this->cache = $cache;
        $this->flashBag = $flashBag;
        $this->applicationService = $applicationService;
    }

    public function verifySignature(array $data, string $parameters): array
    {
        if (!isset($data['saml:Issuer'])) {
            return [new DigiDException('The element \'saml:Issuer\' is missing in your SAML Request')];
        }

        $application = $this->applicationService->findApplicationByEntityId($data['saml:Issuer']);
        if (!$application) {
            return [new DigiDException("There is no application registered with the enitityId: {$data['saml:Issuer']}")];
        }

        $message = substr($parameters, strpos($parameters, 'SAMLRequest'), strpos($parameters, '&Signature=') - strpos($parameters, 'SAMLRequest'));
        $signature = explode('&', substr($parameters, strpos($parameters, 'Signature=') + strlen('Signature=')))[0];

        if (!openssl_verify($message, base64_decode(rawurldecode($signature)), openssl_pkey_get_public($application->getCertificate()), 'sha256WithRSAEncryption')) {
            return [new DigiDException('The request signature could not be verified')];
        }

        return [];
    }

    public function checkSaml(array $data): void
    {
        if (!key_exists('@xmlns:saml', $data)) {
            throw new DigiDException('The attribute \'saml\' is missing in your SAML Request');
        } elseif ($data['@xmlns:saml'] != 'urn:oasis:names:tc:SAML:2.0:assertion') {
            throw new DigiDException("The value of attribute 'saml' is not valid. Expected 'urn:oasis:names:tc:SAML:2.0:assertion', got '{$data['@xmlns:saml']}'");
        }
    }

    public function checkSamlp(array $data): void
    {
        if (!key_exists('@xmlns:samlp', $data)) {
            throw new DigiDException('The attribute \'samlp\' is missing in your SAML Request');
        } elseif ($data['@xmlns:samlp'] != 'urn:oasis:names:tc:SAML:2.0:protocol') {
            throw new DigiDException("The value of attribute 'samlp' is not valid. Expected 'urn:oasis:names:tc:SAML:2.0:protocol', got '{$data['@xmlns:samlp']}'");
        }
    }

    public function checkId(array $data): void
    {
        if (!key_exists('@ID', $data)) {
            throw new DigiDException('The attribute \'ID\' is missing in your SAML Request');
        }
    }

    public function checkVersion(array $data): void
    {
        if (!key_exists('@Version', $data)) {
            throw new DigiDException('The attribute \'Version\' is missing in your SAML Request');
        }
    }

    public function checkDestination(array $data): void
    {
        if (!key_exists('@Destination', $data)) {
            throw new DigiDException('The attribute \'Destination\' is missing in your SAML Request');
        } elseif ($data['@Destination'] != $this->parameterBag->get('app_url')) {
            throw new DigiDException("The value of attribute 'Destination' is not valid. Expected '{$this->parameterBag->get('app_url')}', got '{$data['@Destination']}'");
        }
    }

    public function checkProtocolBinding(array $data): void
    {
        if (!key_exists('@ProtocolBinding', $data)) {
            throw new DigiDException('The attribute \'ProtocolBinding\' is missing in your SAML Request');
        } elseif ($data['@ProtocolBinding'] != 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Artifact') {
            throw new DigiDException("The value of attribute 'Destination' is not valid. Expected 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Artifact', got '{$data['@ProtocolBinding']}'");
        }
    }

    public function checkIdPolicyFormat(array $data): void
    {
        if (!key_exists('@Format', $data['samlp:NameIDPolicy'])) {
            throw new DigiDException('The attribute \'Format\' is missing in your SAML Request in element \'samlp:NameIDPolicy\'');
        } elseif ($data['samlp:NameIDPolicy']['@Format'] != 'urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified') {
            throw new DigiDException("The value of attribute 'Format' in element 'samlp:NameIDPolicy' is not valid. Expected 'urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified', got '{$data['@ProtocolBinding']}'");
        }
    }

    public function checkConsumerService(array $data): void
    {
        if (!key_exists('@AssertionConsumerServiceURL', $data)) {
            throw new DigiDException('The attribute \'AssertionConsumerServiceURL\' is missing in your SAML Request');
        }
    }

    public function checkIssuer(array $data): void
    {
        if (!key_exists('saml:Issuer', $data)) {
            throw new DigiDException('The element \'saml:Issuer\' is missing in your SAML Request');
        }
    }

    public function checkNameIdPolicy(array $data): void
    {
        if (!key_exists('samlp:NameIDPolicy', $data)) {
            throw new DigiDException('The element \'samlp:NameIDPolicy\' is missing in your SAML Request');
        }
        $this->checkIdPolicyFormat($data);
    }

    public function checkAuthnContextClassRef(array $data): void
    {
        if (!key_exists('saml:AuthnContextClassRef', $data['samlp:RequestedAuthnContext'])) {
            throw new DigiDException('The element \'saml:AuthnContextClassRef\' in element \'samlp:RequestedAuthnContext\' is missing in your SAML Request');
        } elseif ($data['samlp:RequestedAuthnContext']['saml:AuthnContextClassRef'] != 'urn:oasis:names:tc:SAML:2.0:ac:classes:PasswordProtectedTransport') {
            throw new DigiDException("The value of element 'saml:AuthnContextClassRef' in element 'samlp:RequestedAuthnContext' is not valid. Expected 'urn:oasis:names:tc:SAML:2.0:ac:classes:PasswordProtectedTransport', got '{$data['samlp:RequestedAuthnContext']['saml:AuthnContextClassRef']}'");
        }
    }

    /**
     * @param array $data
     *
     * @throws DigiDException
     */
    public function checkRequestedAuthnContext(array $data): void
    {
        if (!key_exists('samlp:RequestedAuthnContext', $data)) {
            throw new DigiDException('The element \'samlp:RequestedAuthnContext\' is missing in your SAML Request');
        }
        $this->checkAuthnContextClassRef($data);
    }

    public function verifyDigidAttributes(array $data, array &$errors): void
    {
        try {
            $this->checkDestination($data);
        } catch (DigiDException $e) {
            $errors[] = $e;
        }

        try {
            $this->checkProtocolBinding($data);
        } catch (DigiDException $e) {
            $errors[] = $e;
        }

        try {
            $this->checkConsumerService($data);
        } catch (DigiDException $e) {
            $errors[] = $e;
        }
    }

    public function verifyAttributes(array $data, array &$errors): void
    {
        try {
            $this->checkSaml($data);
        } catch (DigiDException $e) {
            $errors[] = $e;
        }

        try {
            $this->checkSamlp($data);
        } catch (DigiDException $e) {
            $errors[] = $e;
        }

        try {
            $this->checkId($data);
        } catch (DigiDException $e) {
            $errors[] = $e;
        }

        try {
            $this->checkVersion($data);
        } catch (DigiDException $e) {
            $errors[] = $e;
        }
        $this->verifyDigidAttributes($data, $errors);
    }

    public function verifyElements(array $data, array &$errors): void
    {
        try {
            $this->checkIssuer($data);
        } catch (DigiDException $e) {
            $errors[] = $e;
        }

        try {
            $this->checkNameIdPolicy($data);
        } catch (DigiDException $e) {
            $errors[] = $e;
        }

        try {
            $this->checkRequestedAuthnContext($data);
        } catch (DigiDException $e) {
            $errors[] = $e;
        }
    }

    /**
     * @param array $data
     */
    public function verifyXml(array $data): array
    {
        $errors = [];
        $this->verifyAttributes($data, $errors);
        $this->verifyElements($data, $errors);

        return $errors;
    }

    /**
     * @param array  $data
     * @param string $parameters
     *
     * @return array
     */
    public function verifyRequest(array $data, string $parameters): array
    {
        return array_merge($this->verifyXml($data), $this->verifySignature($data, $parameters));
    }

    public function getSamlRequest(Request $request): array
    {
        $samlRequest = $request->query->get('SAMLRequest');
        $xml = gzinflate(base64_decode(rawurldecode($samlRequest)));
        $data = $this->xmlEncoder->decode($xml, 'xml');

        return $data;
    }

    public function saveBsnToCache($bsn): string
    {
        $uuid = Uuid::uuid4();
        $hash = md5($uuid->toString());
        $item = $this->cache->getItem('code_'.$hash);
        $item->set($bsn);

        $this->cache->save($item);

        return $hash;
    }

    public function getRelevantSamlData(array $samlRequest): array
    {
        $saml = [
            'issuer'                   => $samlRequest['saml:Issuer'],
            'assertionConsumerService' => $samlRequest['@AssertionConsumerServiceURL'],
            'providerName'             => $samlRequest['ProviderName'] ?? null,
        ];
        if (filter_var($saml['assertionConsumerService'], FILTER_VALIDATE_URL)) {
            $saml['endpoint'] = $saml['assertionConsumerService'];
        } else {
            //handle Assertion
        }

        return $saml;
    }

    /**
     * @param Request $request
     *
     * @return array
     */
    public function handle(Request $request): array
    {
        $samlRequest = $this->getSamlRequest($request);
        if ($request->query->has('validatedigid') && $request->query->get('validatedigid') == 'true') {
            $errors = $this->verifyRequest($samlRequest, explode('?', $request->getRequestUri())[1]);
            foreach ($errors as $error) {
                $this->flashBag->add('warning', $error->getMessage());
            }
        }
        $saml = $this->getRelevantSamlData($samlRequest);

        return $saml;
    }

    public function buildArtifactResponse($bsn, $artifact)
    {
        $uuid = Uuid::uuid4();
        $artifact = preg_replace("/\s+/", '', $artifact);

        $message = [
            '@xmlns:soapenv' => 'http://schemas.xmlsoap.org/soap/envelope/',
            'soapenv:Body'   => [
                'samlp:ArtifactResponse' => [
                    '@xmlns:samlp'  => 'urn:oasis:names:tc:SAML:2.0:protocol',
                    '@xmlns:saml'   => 'urn:oasis:names:tc:SAML:2.0:assertion',
                    '@xmlns:ds'     => 'http://www.w3.org/2000/09/xmldsig#',
                    '@xmlns:ec'     => 'http://www.w3.org/2001/10/xml-exc-c14n#',
                    '@ID'           => $uuid->toString(),
                    '@Version'      => '2.0',
                    '@IssueInstant' => date('Y-m-d H:i:s'),
                    '@InResponseTo' => $artifact,
                    'saml:Issuer'   => $this->parameterBag->get('app_url'),
                    'samlp:Status'  => [
                        'samlp:StatusCode' => [
                            '@Value' => 'urn:oasis:names:tc:SAML:2.0:status:Success',
                        ],
                    ],
                    'samlp:Response' => [
                        '@InResponseTo'  => $artifact,
                        '@Version'       => '2.0',
                        '@ID'            => $uuid->toString(),
                        '@IssueInstant'  => date('Y-m-d H:i:s'),
                        'saml:Issuer'    => $this->parameterBag->get('app_url'),
                        'saml:Assertion' => [
                            'saml:Subject' => [
                                'saml:NameID'              => 's00000000:'.$bsn,
                                'saml:SubjectConfirmation' => [
                                    '@Method'                      => 'urn:oasis:names:tc:SAML:2.0:cm:bearer',
                                    'saml:SubjectConfirmationData' => [
                                        '@InResponseTo' => $artifact,
                                        '@Recipient'    => rtrim($this->parameterBag->get('app_url'), '/').'/artifact',
                                        '@NotOnOrAfter' => date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s').' +2 minutes')),
                                    ],
                                ],
                            ],
                        ],
                        'saml:Condictions' => [
                            '@NotBefore'               => date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s').' -2 minutes')),
                            '@NotOnOrAfter'            => date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s').' +2 minutes')),
                            'saml:AudienceRestriction' => [
                                'saml:Audience' => $artifact,
                            ],
                        ],
                    ],
                ],
            ],
        ];

        return $this->xmlEncoder->encode($message, 'xml', ['remove_empty_tags' => true]);
    }

    public function retrieveFromCache($artifact): string
    {
        $item = $this->cache->getItem('code_'.preg_replace("/\s+/", '', $artifact));
        if (!$item->isHit()) {
            throw new HttpException('404', 'Artifact not found');
        }

        return $item->get();
    }

    public function handleArtifact(string $xml)
    {
        $array = $this->xmlEncoder->decode($xml, 'xml');

        if (!isset($array['soapenv:Body']['samlp:ArtifactResolve']['samlp:Artifact'])) {
            throw new HttpException('404', 'Artifact not found');
        }
        $bsn = $this->retrieveFromCache($array['soapenv:Body']['samlp:ArtifactResolve']['samlp:Artifact']);

        return $this->buildArtifactResponse($bsn, $array['soapenv:Body']['samlp:ArtifactResolve']['samlp:Artifact']);
    }

    public function retrieveBsn(string $xml)
    {
        $array = $this->xmlEncoder->decode($xml, 'xml');

        if (!isset($array['soapenv:Body']['samlp:ArtifactResolve']['samlp:Artifact'])) {
            throw new HttpException('404', 'Artifact not found');
        }

        return $this->retrieveFromCache($array['soapenv:Body']['samlp:ArtifactResolve']['samlp:Artifact']);
    }

    public function getReferences(): array
    {
        return [
            '@URI'  => '#625cd944-20cf-4296-aafc-d74ea2c40542',

        ];
    }

    public function getSignature(): array
    {
        return [
            'SignedInfo'    => [
                'CanonicalizationMethod'    => ['@Algorithm' => 'http://www.w3.org/2001/10/xml-exc-c14n#'],
                'SignatureMethod'           => ['@Algorithm' => 'http://www.w3.org/2001/04/xmldsig-more#rsa-sha256'],
                'ds:Reference'              => $this->getReferences(),
            ],
        ];
    }

    public function getKeyDescriptor(string $use): array
    {
        return [
            '@use'          => $use,
            'ds:KeyInfo'    => [
                'ds:KeyName'    => '399b859d-09a8-4d58-8306-5d8aface04dd',
                'ds:X509Data'   => [
                    'ds:X509Certificate'    => str_replace(["-----BEGIN CERTIFICATE-----\n", "\n-----END CERTIFICATE-----"], '', $this->parameterBag->get('app_x509_cert')),
                ],
            ],
        ];
    }

    public function getArtifactResolutionService(): array
    {
        return [
            '@Binding'   => 'urn:oasis:names:tc:SAML:2.0:bindings:SOAP',
            '@Location'  => $this->parameterBag->get('app_url').'/artifact',
            '@index'     => '0',
        ];
    }

    public function getSingleLogoutService(): array
    {
        return [
            '@Binding'  => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
            '@Location' => $this->parameterBag->get('app_url').'/logout',
        ];
    }

    public function getSingleSignOnService(): array
    {
        return [
            [
                '@Binding'  => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
                '@Location' => $this->parameterBag->get('app_url'),
            ],
            [
                '@Binding'  => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
                '@Location' => $this->parameterBag->get('app_url'),
            ],
        ];
    }

    public function getIdpSsoDescriptor(): array
    {
        return [
            '@protocolSupportEnumeration'   => 'urn:oasis:names:tc:SAML:2.0:protocol',
            'md:KeyDescriptor'              => [
                $this->getKeyDescriptor('signing'),
                $this->getKeyDescriptor('encryption'),
            ],
            'md:ArtifactResolutionService'  => $this->getArtifactResolutionService(),
            'md:SingleLogoutService'        => $this->getSingleLogoutService(),
            'md:SingleSignOnService'        => $this->getSingleSignOnService(),

        ];
    }

    public function generateMetadataFile(): string
    {
        $data = [
            '@xmlns:md'           => 'urn:oasis:names:tc:SAML:2.0:metadata',
            '@xmlns:ds'           => 'http://www.w3.org/2000/09/xmldsig#',
            '@xmlns:ec'           => 'http://www.w3.org/2001/10/xml-exc-c14n#',
            '@ID'                 => '625cd944-20cf-4296-aafc-d74ea2c40542',
            '@entityId'           => $this->parameterBag->get('app_url').'/saml/metadata',
            'md:IDPSSODescriptor' => $this->getIdpSsoDescriptor(),
        ];
        $xml = $this->xmlEncoder->encode($data, 'xml', ['xml_root_node_name' => 'md:EntityDescriptor']);

        return Utils::addSign($xml, $this->parameterBag->get('app_rsa_key'), $this->parameterBag->get('app_x509_cert'));
    }
}
