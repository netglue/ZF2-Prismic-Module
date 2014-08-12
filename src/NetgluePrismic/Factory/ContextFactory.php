<?php

namespace NetgluePrismic\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use NetgluePrismic\Context;

class ContextFactory implements FactoryInterface
{

    /**
     * Return Context to store a global selected repository ref (Or the master)
     * @param ServiceLocatorInterface $serviceLocator
     * @return Context
     * @throws \RuntimeException
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $api = $serviceLocator->get('Prismic\Api');
        $context = new Context;
        $context->setPrismicApi($api);
        return $context;
    }

}
