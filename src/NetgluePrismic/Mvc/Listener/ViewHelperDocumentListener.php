<?php

/**
 * The purpose of this listener is to receive the setDocument event that gets
 * triggered in the prismic controller plugin and provide that document to the
 * prismic view helper
 */

namespace NetgluePrismic\Mvc\Listener;

use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\ListenerAggregateTrait;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\EventInterface;
use Zend\View\HelperPluginManager;

class ViewHelperDocumentListener implements ListenerAggregateInterface
{

    use ListenerAggregateTrait;

    /**
     * @var HelperPluginManager View Helper Plugin Manager
     */
    protected $helperManager;

    /**
     * Construct the listener. Requires the view helper plugin manager
     * @param  HelperPluginManager $helperManager
     * @return void
     */
    public function __construct(HelperPluginManager $helperManager)
    {
        $this->helperManager = $helperManager;
    }

    /**
     * Attach to specific events with the shared manager
     * @param  EventManagerInterface $events
     * @return void
     */
    public function attach(EventManagerInterface $events)
    {
        $shared = $events->getSharedManager();
        $this->listeners[] = $shared->attach('NetgluePrismic\Mvc\Controller\Plugin\Prismic', 'setDocument', array($this, 'onSetDocument'));
    }

    /**
     * Document is Set Callback
     * @param  EventInterface $event
     * @return void
     */
    public function onSetDocument(EventInterface $event)
    {
        $helper = $this->helperManager->get('Prismic');
        $helper->setDocument($event->getParam('document'));
    }

}
