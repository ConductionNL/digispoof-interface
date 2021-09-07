<?php


namespace App\Service;


use App\Entity\Application;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ApplicationService
{

    private ParameterBagInterface $parameterBag;
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager, ParameterBagInterface $parameterBag)
    {
        $this->parameterBag = $parameterBag;
        $this->entityManager = $entityManager;
    }

    public function findApplicationByEntityId(string $entityId): ?Application
    {
        $applications = $this->entityManager->getRepository("App:Application")->findBy(['entityId' => $entityId], ['dateModified' => 'ASC']);
        if(count($applications) == 0){
            return null;
        }
        $application = end($applications);
        if($application instanceof Application){
            return $application;
        }
        return null;
    }

    public function findApplicationById(string $id): ?Application
    {
        $application = $this->entityManager->getRepository("App:Application")->findOneBy(['id' => $id]);
        if($application instanceof Application){
            return $application;
        }
        return null;
    }


    /**
     * @return string|null
     */
    public function generatePrivateKey(): ?string
    {
        $key = openssl_pkey_new([
            'digest_alg'        =>  'sha256',
            'private_key_bits'  =>  2048,
            'private_key_type'  =>  OPENSSL_KEYTYPE_RSA,
        ]);
        if($key !== false){
             openssl_pkey_export($key, $out);
             return $out;
        }
        return null;
    }

    public function createCert(Application $application, array $properties = []): string
    {
        $dn = [
            'countryName'           => isset($properties['country']) && strlen($properties['country']) == 2 ?  $properties['country'] : 'NL',
            'stateOrProvinceName'   => isset($properties['province']) ? $properties['province'] : 'Noord-Holland',
            'localityName'          => isset($properties['locality']) ? $properties['locality'] : 'Amsterdam',
            'organizationName'      => isset($properties['organization']) ? $properties['organization'] : 'Conduction B.V.',
            'commonName'            => $application->getEntityId(),
            'emailAddress'          => $application->getEmailAddress(),
        ];
        $privateKey = $application->getPrivateKey();
        $csr = openssl_csr_new($dn, $privateKey, ['digest_alg' => 'sha256']);
        $x509 = openssl_csr_sign($csr, null, $privateKey, 365, ['digest_alg' => 'sha256']);

        openssl_x509_export($x509, $output);
        return $output;
    }

    public function generateCertificate(Application $application): Application
    {
        $application->setPrivateKey($this->generatePrivateKey());
        $application->setCertificate($this->createCert($application));

        return $application;
    }

    public function createApplication(array $applicationArray): Application
    {
        if($this->findApplicationByEntityId($applicationArray['entityId']) != null){
            throw new \Exception('An application with that entity id already exists');
        }
        $application = new Application();
        $application->setName($applicationArray['name']);
        $application->setEntityId($applicationArray['entityId']);
        $application->setEmailAddress($applicationArray['emailAddress']);

        if($applicationArray['certificate']) {
            $application->setCertificate($applicationArray['certificate']);
        } else {
            $application = $this->generateCertificate($application);
        }

        $this->entityManager->persist($application);
        $this->entityManager->flush();

        return $application;
    }
}