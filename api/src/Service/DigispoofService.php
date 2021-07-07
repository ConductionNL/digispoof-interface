<?php


namespace App\Service;


use Conduction\CommonGroundBundle\Service\CommonGroundService;

class DigispoofService
{
    private CommonGroundService $commonGroundService;

    public function __construct(CommonGroundService $commonGroundService)
    {
        $this->commonGroundService = $commonGroundService;
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
}
