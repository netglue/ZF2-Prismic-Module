<?php

namespace NetgluePrismic\Mvc\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use NetgluePrismic\Mvc\Listener\SelectedRefListener;

class SelectedRefListenerFactory implements FactoryInterface
{

    /**
     * Return the preview cookie listener
     * @param  ServiceLocatorInterface $serviceLocator
     * @return SelectedRefListener
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $listener = new SelectedRefListener;
        $context = $serviceLocator->get('NetgluePrismic\Context');
        $session = $serviceLocator->get('NetgluePrismic\Session\PrismicContainer');
        $listener->setContext($context);
        $listener->setSession($session);

        return $listener;
    }
}
