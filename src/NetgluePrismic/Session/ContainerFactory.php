<?php
namespace NetgluePrismic\Session;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use NetgluePrismic\Mvc\Router\RouterOptions;

class ContainerFactory implements FactoryInterface
{

    /**
     * Return Prismic Session Container
     * @param ServiceLocatorInterface $serviceLocator
     * @return PrismicContainer
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('Config');
        $config = isset($config['prismic']['session']) ? $config['prismic']['session'] : array('name' => 'Prismic');

        $container = new PrismicContainer($config['name']);
        $container->setContext($serviceLocator->get('Prismic\Context'));
        return $container;
    }

}
