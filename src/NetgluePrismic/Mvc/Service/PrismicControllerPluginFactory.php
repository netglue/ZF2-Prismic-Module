<?php

namespace NetgluePrismic\Mvc\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use NetgluePrismic\Mvc\Controller\Plugin\Prismic;

class PrismicControllerPluginFactory implements FactoryInterface
{

    /**
     * Return Prismic controller plugin
     * @param  ServiceLocatorInterface $controllerPluginManager
     * @return Prismic
     */
    public function createService(ServiceLocatorInterface $controllerPluginManager)
    {
        $serviceLocator = $controllerPluginManager->getServiceLocator();
        $context = $serviceLocator->get('Prismic\Context');
        $routingOptions = $serviceLocator->get('NetgluePrismic\Mvc\Router\RouterOptions');
        $linkResolver = $serviceLocator->get('NetgluePrismic\Mvc\LinkResolver');

        $plugin = new Prismic;
        $plugin->setContext($context);
        $plugin->setPrismicApi($context->getPrismicApi());
        $plugin->setRouterOptions($routingOptions);
        $plugin->setLinkResolver($linkResolver);

        return $plugin;
    }

}
