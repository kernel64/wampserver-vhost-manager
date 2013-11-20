<?php

namespace Mabs\WampVHostBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

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
        if ($request->isXmlHttpRequest() && $request->isMethod('POST')) {
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
        $form = $this->createFormBuilder()
                ->add('config', 'textarea', array(
                    'constraints' => array(
                        new NotBlank(),
                        new Regex(array(
                            'pattern' => "#<VirtualHost \*:[0-9]*>((.*)|\n*)*</VirtualHost>$#",
                            'message' => "Invalid configuration."
                            )))
                ))
                ->getForm();

        if ($request->isXmlHttpRequest() && $request->isMethod('POST')) {

            $form->handleRequest($request);
            if ($form->isValid()) {
                $data = $form->getData();

                $vHost = $this->get('mabs_wamp_v_host.manager');
                $vHost->updateVHost($filename, $data['config']);

                return $this->render('MabsWampVHostBundle:Vhost:empty.html.twig');
            }
        }

        return $this->render('MabsWampVHostBundle:Vhost:show.html.twig', array('filename' => $filename, 'form' => $form->createView()));
    }

    public function showAction($filename)
    {
        $vHost = $this->get('mabs_wamp_v_host.manager');
        $data = array('config' => $vHost->showConfig($filename));
        $form = $this->createFormBuilder($data)
                ->add('config', 'textarea', array(
                    'constraints' => new NotBlank()
                ))
                ->getForm();

        return $this->render('MabsWampVHostBundle:Vhost:show.html.twig', array('filename' => $filename, 'form' => $form->createView()));
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
