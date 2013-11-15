<?php

namespace Mabs\WampVHostBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('MabsWampVHostBundle:Default:index.html.twig');
    }
    
    public function restartAction() {
    	
    	$this->get('mabs_wamp_v_host.manager')->restartServers();
    	
    	return $this->redirect($this->generateUrl('homepage'), 301);
    }
}