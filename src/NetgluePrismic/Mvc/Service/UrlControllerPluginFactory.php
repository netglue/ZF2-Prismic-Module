<?php

namespace NetgluePrismic\Mvc\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use NetgluePrismic\Mvc\Controller\Plugin\Url;

class UrlControllerPluginFactory implements FactoryInterface
{

    /**
     * Return Prismic Url Plugin
     * @param  ServiceLocatorInterface $controllerPluginManager
     * @return Url
     */
    public function createService(ServiceLocatorInterface $controllerPluginManager)
    {
        $serviceLocator = $controllerPluginManager->getServiceLocator();

        $linkResolver  = $serviceLocator->get('NetgluePrismic\Mvc\LinkResolver');
        $linkGenerator = $serviceLocator->get('NetgluePrismic\Mvc\LinkGenerator');

        $helper = new Url($linkResolver, $linkGenerator);

        return $helper;
    }

}
