<?php

// src/Controller/DashboardController.php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use App\Service\CommonGroundService;

/**
 * Class PitchController
 * @package App\Controller
 * @Route("/pitches")
 */
class PitchController extends AbstractController
{

    /**
     * @Route("/")
     * @Template
     */
    public function indexAction()
    {
        $pitches = [];

        for ($i = 1; $i < 4; $i++) {

            $pitch = [];
            $pitch["id"] = $i;
            $pitch['name'] = "Een betere wereld voor iedereen";
            $pitch['description'] = "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc eu faucibus odio, nec
                                molestie orci. Phasellus sollicitudin cursus ullamcorper.  Nunc quis sapien non felis
                                 bibendum tempor et id tortor. Mauris felis sapien, condimentum ullamcorper tincidunt 
                                 sit amet, commodo sed ex. Nam malesuada quis neque sit amet imperdiet. Ut nec est 
                                 pharetra leo varius varius ac id orci. Nulla euismod vestibulum eros eu sagittis.";
            $pitches[] = $pitch;
        }

        return ["pitches" => $pitches];
    }

    /**
     * @Route("/{id}")
     * @Template
     */
    public function viewAction($id)
    {
        $pitch = [];
        $pitch['name'] = "Een betere wereld voor iedereen";
        $pitch['description'] = "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc eu faucibus odio, nec
                                molestie orci. Phasellus sollicitudin cursus ullamcorper.  Nunc quis sapien non felis
                                 bibendum tempor et id tortor. Mauris felis sapien, condimentum ullamcorper tincidunt 
                                 sit amet, commodo sed ex. Nam malesuada quis neque sit amet imperdiet. Ut nec est 
                                 pharetra leo varius varius ac id orci. Nulla euismod vestibulum eros eu sagittis.";
        return ["pitch" => $pitch];
    }
}






