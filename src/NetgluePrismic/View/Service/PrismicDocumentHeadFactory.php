<?php

namespace NetgluePrismic\View\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use NetgluePrismic\View\Helper\PrismicDocumentHead as Helper;

class PrismicDocumentHeadFactory implements FactoryInterface
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

        /**
         * Set internal -> document property map
         */
        if(isset($config['prismic']['documentHeadViewHelper']['propertyMap'])) {
            $map = $config['prismic']['documentHeadViewHelper']['propertyMap'];
            $helper->setPropertyMap($map);
        }

        return $helper;
    }

}
