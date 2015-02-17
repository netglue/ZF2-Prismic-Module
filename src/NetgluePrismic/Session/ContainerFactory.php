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
        $context = $serviceLocator->get('NetgluePrismic\Context');
        $container->setContext($context);

        if($container->hasAccessToken()) {
            $context->setPrivilegedAccess(true);
        }
        return $container;
    }

}
