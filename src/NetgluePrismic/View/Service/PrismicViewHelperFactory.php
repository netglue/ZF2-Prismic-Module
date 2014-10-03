<?php

namespace NetgluePrismic\View\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use NetgluePrismic\View\Helper\Prismic as Helper;

class PrismicViewHelperFactory implements FactoryInterface
{

    /**
     * Return Prismic head meta view helper
     * @param ServiceLocatorInterface $controllerPluginManager
     * @return Helper
     */
    public function createService(ServiceLocatorInterface $viewPluginManager)
    {
        $serviceLocator = $viewPluginManager->getServiceLocator();
        $config = $serviceLocator->get('config');

        $helper = new Helper;
        $helper->setContext($serviceLocator->get('Prismic\Context'));

        return $helper;
    }

}
