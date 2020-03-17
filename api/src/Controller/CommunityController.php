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
 * Class DefaultController
 * @package App\Controller
 * @Route("/community")
 */
class CommunityController extends AbstractController
{

    /**
     * @Route("/")
     * @Template
     */
	public function indexAction()
    {	
    	return [];
    }
}






