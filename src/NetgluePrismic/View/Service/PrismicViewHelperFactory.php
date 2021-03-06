<?php

namespace NetgluePrismic\View\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use NetgluePrismic\View\Helper\Prismic as Helper;

class PrismicViewHelperFactory implements FactoryInterface
{

    /**
     * Return Prismic (Document) view helper
     * @param  ServiceLocatorInterface $viewPluginManager
     * @return Helper
     */
    public function createService(ServiceLocatorInterface $viewPluginManager)
    {
        $serviceLocator = $viewPluginManager->getServiceLocator();

        $linkResolver = $serviceLocator->get('NetgluePrismic\Mvc\LinkResolver');

        $helper = new Helper;
        $helper->setLinkResolver($linkResolver);

        return $helper;
    }

}
