<?php

namespace NetgluePrismic\View\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use NetgluePrismic\View\Helper\Url;

class UrlViewHelperFactory implements FactoryInterface
{

    /**
     * Return Prismic Url Helper
     * @param  ServiceLocatorInterface $controllerPluginManager
     * @return Url
     */
    public function createService(ServiceLocatorInterface $viewPluginManager)
    {
        $serviceLocator = $viewPluginManager->getServiceLocator();

        $linkResolver  = $serviceLocator->get('NetgluePrismic\Mvc\LinkResolver');
        $linkGenerator = $serviceLocator->get('NetgluePrismic\Mvc\LinkGenerator');

        $helper = new Url($linkResolver, $linkGenerator);

        return $helper;
    }

}
