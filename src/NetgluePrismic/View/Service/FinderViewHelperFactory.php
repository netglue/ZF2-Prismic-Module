<?php

namespace NetgluePrismic\View\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use NetgluePrismic\View\Helper\Finder;

class FinderViewHelperFactory implements FactoryInterface
{

    /**
     * Return Prismic Finder Helper
     * @param  ServiceLocatorInterface $controllerPluginManager
     * @return Finder
     */
    public function createService(ServiceLocatorInterface $viewPluginManager)
    {
        $serviceLocator = $viewPluginManager->getServiceLocator();

        $context = $serviceLocator->get('NetgluePrismic\Context');

        $helper = new Finder($context);

        return $helper;
    }

}
