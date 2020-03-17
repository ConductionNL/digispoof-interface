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
 * Class ArtikelController
 * @package App\Controller
 * @Route("/articles")
 */
class ArtikelController extends AbstractController
{

    /**
     * @Route("/")
     * @Template
     */
	public function indexAction()
    {
        $artikelen = [];

        for($i=1;$i<4;$i++) {

            $artikel = [];
            $artikel['name'] = "Whitepaper Kwaliteitsaanpak ICTU Software Realisatie";
            $artikel['description'] = "Met verregaande digitalisering wordt goede en betrouwbare software meer en meer belangrijk. Ook binnen de overheid. Daar ligt ook een uitdaging. ICTU wil met haar kwaliteitsaanpak daarbij helpen.";
            $artikel['createdAt'] = "20-1-2020";
            $artikel['source'] = "https://www.ictu.nl/";

            $artikelen[] = $artikel;
        }

    	return ["artikelen"=>$artikelen];
    }

    /**
     * @Route("/{id}")
     * @Template
     */
	public function viewAction($id)
    {
       $artikel = [];
       $artikel['name'] = "Whitepaper Kwaliteitsaanpak ICTU Software Realisatie";
       $artikel['description'] = "Met verregaande digitalisering wordt goede en betrouwbare software meer en meer belangrijk. Ook binnen de overheid. Daar ligt ook een uitdaging. ICTU wil met haar kwaliteitsaanpak daarbij helpen.";
       $artikel['createdAt'] = "20-1-2020";
       $artikel['source'] = "https://www.ictu.nl/";


    	return ["artikel"=>$artikel];
    }

}






