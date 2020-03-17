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
 * Class DeveloperController
 * @package App\Controller
 * @Route("/developer")
 */
class DeveloperController extends AbstractController
{

    /**
     * @Route("/")
     * @Template
     */
    public function dashboardAction()
    {
        $artikelen = [];

        $artikel = [];
        $artikel['id'] = 1;
        $artikel['name'] = "Artikel";
        $artikel['description'] = "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc eu faucibus odio, nec
                    molestie orci. Phasellus sollicitudin cursus ullamcorper.";
        $artikel['writer'] = "John Doe";
        $artikel['createdAt'] = "20-1-2020";

        $artikelen[] = $artikel;

        $artikel = [];
        $artikel['id'] = 2;
        $artikel['name'] = "Artikel";
        $artikel['description'] = "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc eu faucibus odio, nec
                    molestie orci. Phasellus sollicitudin cursus ullamcorper.";
        $artikel['writer'] = "John Doe";
        $artikel['createdAt'] = "20-1-2020";

        $artikelen[] = $artikel;

        $artikel = [];
        $artikel['id'] = 3;
        $artikel['name'] = "Artikel";
        $artikel['description'] = "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc eu faucibus odio, nec
                    molestie orci. Phasellus sollicitudin cursus ullamcorper.";
        $artikel['writer'] = "John Doe";
        $artikel['createdAt'] = "20-1-2020";

        $artikelen[] = $artikel;

        $challenges = [];
        for ($i = 1; $i < 7; $i++) {
            $challenge = [];
            $challenge['id'] = $i;
            $challenge['name'] = "Challenge";
            $challenge['description'] = "Dit probleem moet echt worden opglost";
            $challenges[] = $challenge;
        }

        return ["artikelen" => $artikelen, "challenges" => $challenges];
    }

    /**
     * @Route("/all")
     * @Template
     */
    public function indexAction()
    {
        return [];
    }

    /**
     * @Route("/me")
     * @Template
     */
    public function meAction()
    {
        $events = [];

        for ($i = 1; $i < 4; $i++) {
            $event = [];
            $event["id"] = $i;
            $event['name'] = "Event";
            $event['description'] = "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc eu faucibus odio, nec
                                molestie orci. Phasellus sollicitudin cursus ullamcorper.";
            $events[] = $event;
        }

        $pitches = [];

        for ($i = 1; $i < 4; $i++) {

            $pitch = [];
            $pitch["id"] = $i;
            $pitch['name'] = "Een betere wereld voor iedereen";
            $pitch['description'] = "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc eu faucibus odio, nec
                                molestie orci. Phasellus sollicitudin cursus ullamcorper.";
            $pitches[] = $pitch;
        }

        $repositories = [];
        
        for ($i = 1; $i < 4; $i++) {
        	
        	$repository= [];
        	$repository["id"] = $i;
        	$repository['name'] = "Mijn eerste applicatie";
        	$repository['description'] = "Dit is echt een top applicatie die de wereld gaat verbeteren";
        	$repositories[] = $repository;
        }
        
        
        return ["events" => $events, "pitches" => $pitches, 'repositories' => $repositories];
    }

    /**
     * @Route("/toolkit")
     * @Template
     */
    public function toolkitAction()
    {
        return [];
    }

    /**
     * @Route("/{id}")
     * @Template
     */
    public function viewAction($id)
    {
        return [];
    }
}






