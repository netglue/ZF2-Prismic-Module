<?php

namespace NetgluePrismic\Mvc\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use NetgluePrismic\Mvc\Listener\HeadMetaListener;

class HeadMetaListenerFactory implements FactoryInterface
{

    /**
     * Return configured HeadMetaListener
     * @param  ServiceLocatorInterface $serviceLocator
     * @return HeadMetaListener
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $viewManager   = $serviceLocator->get('HttpViewManager');
        $helperManager = $viewManager->getHelperManager();
        $listener = new HeadMetaListener($helperManager);
        $config = $serviceLocator->get('config');
        if (isset($config['prismic']['HeadMetaListener'])) {
            $listener->setOptions($config['prismic']['HeadMetaListener']);
        }

        return $listener;
    }

}
