<?php

// src/Controller/DefaultController.php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use Conduction\CommonGroundBundle\Service\CommonGroundService;

/**
 * Class UserController.
 */
class UserController extends AbstractController
{
    /**
     * @Route("/login", methods={"GET"})
     * @Template
     */
    public function login(Request $request, CommonGroundService $commonGroundService, ParameterBagInterface $params, EventDispatcherInterface $dispatcher)
    {
        return [];
    }

    /**
     * @Route("/logout", methods={"GET"})
     * @Template
     */
    public function logout(Request $request, CommonGroundService $commonGroundService, ParameterBagInterface $params, EventDispatcherInterface $dispatcher)
    {
        return [];
    }
}
