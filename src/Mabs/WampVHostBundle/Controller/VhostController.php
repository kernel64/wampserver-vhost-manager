<?php

namespace Mabs\WampVHostBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Validator\Constraints\NotBlank;

class VhostController extends Controller
{
    public function newAction()
    {
    	$request = $this->getRequest();
        $form = $this->createFormBuilder()
        ->add('servername', 'text', array(
                                    'constraints' => new NotBlank()
                                           ))
        ->add('documentroot', 'text', array(
                                    'constraints' => new NotBlank()
                                           ))
        ->getForm();
    	if($request->isXmlHttpRequest() && $request->isMethod('POST')) {	
    		$vHost = $this->get('mabs_wamp_v_host.manager');            
                $form->handleRequest($request);
                if ($form->isValid()) {              
                    $data = $form->getData();     
                    $vHost->createNewVHost($data['servername'], $data['documentroot']);
	
                    return $this->render('MabsWampVHostBundle:Vhost:empty.html.twig');      
                }
    	}
    	
        return $this->render('MabsWampVHostBundle:Vhost:new.html.twig', array('form' => $form->createView()));
    }
    
    public function updateAction($filename)
    {
    	$request = $this->getRequest();
    
    	if(!$request->isXmlHttpRequest() && $request->isMethod('POST')) {
    
    		$vHost = $this->get('mabs_wamp_v_host.manager');
    		$config    = $request->request->get('config');
    
    		$vHost->updateVHost($filename, $config);
    
    		return $this->redirect($this->generateUrl('homepage'), 301);
    	}
    	 
    	return $this->render('MabsWampVHostBundle:Vhost:show.html.twig', array('filename' => $filename, 'data' => $config));
    }
    
    
    public function showAction($filename)
    {
    	$vHost = $this->get('mabs_wamp_v_host.manager');
    	$config = $vHost->showConfig($filename);
    	
    	return $this->render('MabsWampVHostBundle:Vhost:show.html.twig', array('filename' => $filename, 'data' => $config));
    }
    
    public function deleteAction($filename)
    {
        $vHost = $this->get('mabs_wamp_v_host.manager');
        $config = $vHost->deleteVHost($filename);
        
        return $this->listAction();
    }
    
    public function listAction()
    {
    	$vHost = $this->get('mabs_wamp_v_host.manager');
    	
    	return $this->render('MabsWampVHostBundle:Vhost:list.html.twig', array('vhosts' => $vHost->getList()));
    }
}