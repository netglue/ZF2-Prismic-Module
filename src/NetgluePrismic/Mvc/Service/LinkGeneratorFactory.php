<?php

namespace NetgluePrismic\Mvc\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use NetgluePrismic\Mvc\LinkGenerator;

class LinkGeneratorFactory implements FactoryInterface
{

    /**
     * Return LinkGenerator
     * @param  ServiceLocatorInterface $serviceLocator
     * @return LinkGenerator
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $context = $serviceLocator->get('NetgluePrismic\Context');

        return new LinkGenerator($context);
    }

}
