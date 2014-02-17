<?php

namespace Thumbnizer\ProcessingBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('ThumbnizerProcessingBundle:Default:index.html.twig', array('name' => $name." woot"));
    }
}
