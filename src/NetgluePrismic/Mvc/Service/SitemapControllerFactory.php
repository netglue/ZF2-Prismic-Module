<?php

namespace NetgluePrismic\Mvc\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use NetgluePrismic\Mvc\Controller\SitemapController;

class SitemapControllerFactory implements FactoryInterface
{

    /**
     * Return SitemapController
     * @param  ServiceLocatorInterface $controllerManager
     * @return SitemapController
     */
    public function createService(ServiceLocatorInterface $controllerManager)
    {
        $serviceLocator = $controllerManager->getServiceLocator();

        $controller = new SitemapController;
        $controller->setSitemapService($serviceLocator->get('NetgluePrismic\Service\Sitemap'));

        return $controller;
    }

}
