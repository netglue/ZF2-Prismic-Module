<?php

namespace NetgluePrismic\Mvc\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use NetgluePrismic\Mvc\Listener\ToolbarListener;

class ToolbarListenerFactory implements FactoryInterface
{

    /**
     * Return the toolbar listener
     * @param  ServiceLocatorInterface $serviceLocator
     * @return ToolbarListener
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {

        $viewRenderer = $serviceLocator->get('ViewRenderer');

        $toolbar = new ToolbarListener($viewRenderer, $serviceLocator);

        /**
         * If the context reports that we have privileged access
         * mark the toolbar as 'should render'
         */
        $context = $serviceLocator->get('NetgluePrismic\Context');
        if($context->getPrivilegedAccess()) {
            $toolbar->setShouldRender(true);
        }

        return $toolbar;
    }
}
