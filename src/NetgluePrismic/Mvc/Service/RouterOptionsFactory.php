<?php

namespace NetgluePrismic\Mvc\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use NetgluePrismic\Mvc\Router\RouterOptions;

class RouterOptionsFactory implements FactoryInterface
{

    /**
     * Return Prismic routing options instance
     * @param ServiceLocatorInterface $serviceLocator
     * @return RouterOptions
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('Config');
        $config = isset($config['prismic']['routeParameters']) ? $config['prismic']['routeParameters'] : array();
        $options = new RouterOptions($config);
        return $options;
    }

}
