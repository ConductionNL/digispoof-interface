<?php

namespace App\Service;

use App\Exception\DigiDException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Encoder\XmlEncoder;

class DigiDMockService
{
    private XmlEncoder $xmlEncoder;
    private ParameterBagInterface $parameterBag;

    public function __construct(ParameterBagInterface $parameterBag)
    {
        $this->xmlEncoder = new XmlEncoder([]);
        $this->parameterBag = $parameterBag;
    }

    public function verifySignature(string $certificate, string $parameters): bool
    {
        return true;
    }

    public function checkSaml(array $data): void
    {
        if(!key_exists('@xmlns:saml', $data)){
            throw new DigiDException('The attribute \'saml\' is missing in your SAML Request');
        } elseif ($data['@xmlns:saml'] != "urn:oasis:names:tc:SAML:2.0:assertion") {
            throw new DigiDException("The value of attribute 'saml' is not valid. \nGot '{$data['@xmlns:saml']}', \nExpected 'urn:oasis:names:tc:SAML:2.0:assertion'");
        }
    }

    public function checkSamlp(array $data): void
    {
        if(!key_exists('@xmlns:samlp', $data)){
            throw new DigiDException('The attribute \'samlp\' is missing in your SAML Request');
        } elseif ($data['@xmlns:samlp'] != "urn:oasis:names:tc:SAML:2.0:protocol") {
            throw new DigiDException("The value of attribute 'samlp' is not valid. \nGot '{$data['@xmlns:samlp']}', \nExpected 'urn:oasis:names:tc:SAML:2.0:protocol'");
        }
    }

    public function checkId(array $data): void
    {
        if(!key_exists('@ID', $data)){
            throw new DigiDException('The attribute \'ID\' is missing in your SAML Request');
        }
    }

    public function checkVersion(array $data): void
    {
        if(!key_exists('@Version', $data)){
            throw new DigiDException('The attribute \'Version\' is missing in your SAML Request');
        }
    }

    public function checkDestination(array $data): void
    {
        if(!key_exists('@Destination', $data)){
            throw new DigiDException('The attribute \'Destination\' is missing in your SAML Request');
        } elseif ($data['@Destination'] != $this->parameterBag->get("app_url")) {
            throw new DigiDException("The value of attribute 'Destination' is not valid. \nGot '{$data['@Destination']}', \nExpected '{$this->parameterBag->get("app_url")}'");
        }
    }

    public function checkProtocolBinding(array $data): void
    {
        if(!key_exists('@ProtocolBinding', $data)){
            throw new DigiDException('The attribute \'ProtocolBinding\' is missing in your SAML Request');
        } elseif ($data['@ProtocolBinding'] != "urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Artifact") {
            throw new DigiDException("The value of attribute 'Destination' is not valid. \nGot '{$data['@ProtocolBinding']}', \nExpected 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Artifact'");
        }
    }

    public function checkIdPolicyFormat(array $data): void
    {
        if(!key_exists('@Format', $data['samlp:NameIDPolicy'])){
            throw new DigiDException('The attribute \'Format\' is missing in your SAML Request in element \'samlp:NameIDPolicy\'');
        } elseif ($data['samlp:NameIDPolicy']['@Format'] != "urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified") {
            throw new DigiDException("The value of attribute 'Format' in element 'samlp:NameIDPolicy' is not valid. \nGot '{$data['@ProtocolBinding']}', \nExpected 'urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified'");
        }
    }

    public function checkConsumerService(array $data): void
    {
        if(!key_exists('@AssertionConsumerServiceURL', $data)){
            throw new DigiDException('The attribute \'AssertionConsumerServiceURL\' is missing in your SAML Request');
        }
    }

    public function checkIssuer(array $data): void
    {
        if(!key_exists('saml:Issuer', $data)){
            throw new DigiDException('The element \'saml:Issuer\' is missing in your SAML Request');
        }
    }

    public function checkNameIdPolicy(array $data): void
    {
        if(!key_exists('samlp:NameIDPolicy', $data)){
            throw new DigiDException('The element \'samlp:NameIDPolicy\' is missing in your SAML Request');
        }
        $this->checkIdPolicyFormat($data);
    }

    public function checkAuthnContextClassRef(array $data): void
    {
        if(!key_exists('saml:AuthnContextClassRef', $data['samlp:RequestedAuthnContext'])){
            throw new DigiDException('The element \'saml:AuthnContextClassRef\' in element \'samlp:RequestedAuthnContext\' is missing in your SAML Request');
        } elseif ($data['samlp:RequestedAuthnContext']['saml:AuthnContextClassRef'] != "urn:oasis:names:tc:SAML:2.0:ac:classes:PasswordProtectedTransport") {
            throw new DigiDException("The value of element 'saml:AuthnContextClassRef' in element 'samlp:RequestedAuthnContext' is not valid. \nGot '{$data['samlp:RequestedAuthnContext']['saml:AuthnContextClassRef']}', \nExpected 'urn:oasis:names:tc:SAML:2.0:ac:classes:PasswordProtectedTransport'");
        }
    }

    /**
     * @param array $data
     * @throws DigiDException
     */
    public function checkRequestedAuthnContext(array $data): void
    {
        if(!key_exists('samlp:RequestedAuthnContext', $data)){
            throw new DigiDException('The element \'samlp:RequestedAuthnContext\' is missing in your SAML Request');
        }
        $this->checkAuthnContextClassRef($data);
    }

    /**
     * @param array $data
     * @throws DigiDException
     */
    public function verifyAttributes(array $data): void
    {
        try{
            $this->checkSaml($data);
            $this->checkSamlp($data);
            $this->checkId($data);
            $this->checkVersion($data);
            $this->checkDestination($data);
            $this->checkProtocolBinding($data);
            $this->checkConsumerService($data);
            $this->checkIssuer($data);
            $this->checkNameIdPolicy($data);
            $this->checkRequestedAuthnContext($data);
        } catch (DigiDException $e){
            throw $e;
        }
    }

    /**
     * @param array $data
     * @param string $parameters
     * @return bool
     * @throws DigiDException
     */
    public function verifyRequest(array $data, string $parameters): bool
    {

        return $this->verifyAttributes($data) && $this->verifySignature('', $parameters);
    }

    public function getSamlRequest(Request $request): array
    {
        $samlRequest = $request->query->get('SAMLRequest');
        $xml = gzinflate(base64_decode(rawurldecode($samlRequest)));
        $data = $this->xmlEncoder->decode($xml, 'xml');

        return $data;
    }

    /**
     * @param Request $request
     * @throws DigiDException
     */
    public function handle(Request $request): void
    {
        try{
            $samlRequest = $this->getSamlRequest($request);
            $this->verifyRequest($samlRequest, $request->getQueryString());
            die;
        } catch(DigiDException $e){
            echo $e->getMessage();
            die;
            throw $e;
        }
    }
}