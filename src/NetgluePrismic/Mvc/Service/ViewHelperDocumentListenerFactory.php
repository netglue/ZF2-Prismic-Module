<?php

namespace NetgluePrismic\Mvc\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use NetgluePrismic\Mvc\Listener\ViewHelperDocumentListener;

class ViewHelperDocumentListenerFactory implements FactoryInterface
{

    /**
     * Return configured ViewHelperDocumentListener
     * @param ServiceLocatorInterface $serviceLocator
     * @return HeadMetaListener
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $viewManager   = $serviceLocator->get('HttpViewManager');
        $helperManager = $viewManager->getHelperManager();
        $listener = new ViewHelperDocumentListener($helperManager);
        return $listener;
    }

}
