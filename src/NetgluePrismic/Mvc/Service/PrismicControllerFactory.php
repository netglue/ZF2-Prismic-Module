<?php

namespace NetgluePrismic\Mvc\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use NetgluePrismic\Mvc\Controller\PrismicController;

class PrismicControllerFactory implements FactoryInterface
{

    /**
     * Return Prismic controller
     * @param ServiceLocatorInterface $controllerPluginManager
     * @return Prismic
     */
    public function createService(ServiceLocatorInterface $controllerManager)
    {
        $serviceLocator = $controllerManager->getServiceLocator();
        $config = $serviceLocator->get('config');
        $config = isset($config['prismic']) ? $config['prismic'] : array();
        $secret = isset($config['webhookSecret']) ? $config['webhookSecret'] : NULL;

        $controller = new PrismicController;
        $controller->setWebhookSecret($secret);
        return $controller;
    }

}
