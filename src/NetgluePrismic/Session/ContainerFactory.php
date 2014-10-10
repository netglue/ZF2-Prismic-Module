<?php
namespace NetgluePrismic\Session;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ContainerFactory implements FactoryInterface
{

    /**
     * Return Prismic Session Container
     * @param  ServiceLocatorInterface $serviceLocator
     * @return PrismicContainer
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $container = new PrismicContainer('Prismic');
        $container->setContext($serviceLocator->get('Prismic\Context'));

        return $container;
    }

}
