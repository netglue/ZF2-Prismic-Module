<?php

namespace NetgluePrismic\Mvc\Listener;

use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\ListenerAggregateTrait;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\EventInterface;
use Zend\Mvc\MvcEvent;
use NetgluePrismic\ContextAwareTrait;
use NetgluePrismic\ContextAwareInterface;
use NetgluePrismic\Session\PrismicContainer;
use Prismic\Api;

class SelectedRefListener implements
    ListenerAggregateInterface,
    ContextAwareInterface
{

    use ListenerAggregateTrait;
    use ContextAwareTrait;

    private $session;

    /**
     * Attach to specific events with the shared manager
     * @param  EventManagerInterface $events
     * @return void
     */
    public function attach(EventManagerInterface $events)
    {
        $shared = $events->getSharedManager();
        $this->listeners[] = $shared->attach('Zend\Mvc\Application', MvcEvent::EVENT_ROUTE, array($this, 'setPreviewRef'));
    }

    /**
     * If a preview cookie is found, set it as the ref in the global context, otherwise, set the ref that's stored in the session
     * @param EventInterface $event
     * @return void
     */
    public function setPreviewRef(MvcEvent $event)
    {
        $ref = $this->getSessionRef();

        $cookie = $this->getCookieRef($event);

        if(null !== $cookie) {
            $ref = $cookie;
        }

        if(null !== $ref) {
            $this->getContext()->setRefWithString($ref);
        }
    }

    /**
     * Return the ref set in the session if it can be found
     * @return string|null
     */
    public function getSessionRef()
    {
        if($this->session) {
            return $this->session->getRef();
        }
    }

    /**
     * Return the ref stored in the preview cookie if it can be found
     * @return string|null
     */
    public function getCookieRef(MvcEvent $event)
    {
        $request = $event->getApplication()->getRequest();
        $cookies = $request->getHeader('cookie');
        $cookieName = Api::PREVIEW_COOKIE;
        $cookieName = str_replace('.', '_', $cookieName);
        if (isset($cookies->{$cookieName}) && !empty($cookies->{$cookieName})) {

            return $cookies->{$cookieName};
        }

        return null;
    }

    public function setSession(PrismicContainer $session = null)
    {
        $this->session = $session;
    }

}
