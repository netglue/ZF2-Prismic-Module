<?php

namespace NetgluePrismic\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Prismic\Cache\NoCache;

class NoCacheFactory implements FactoryInterface
{

    /**
     * Return a fake cache
     * @param  ServiceLocatorInterface $serviceLocator
     * @return NoCache
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new NoCache;
    }

}
