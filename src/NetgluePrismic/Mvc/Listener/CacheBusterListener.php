<?php

/**
 * This listener looks out for the event triggered by the controller action that receives
 * webhook posts from the api and clears the api cache.
 */

namespace NetgluePrismic\Mvc\Listener;

use NetgluePrismic\Exception;
use NetgluePrismic\ApiAwareInterface;
use NetgluePrismic\ApiAwareTrait;

use Prismic\Cache\CacheInterface;

use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\ListenerAggregateTrait;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\EventInterface;

class CacheBusterListener implements ListenerAggregateInterface, ApiAwareInterface
{

    use ListenerAggregateTrait;

    use ApiAwareTrait;

    /**
     * Event Params from the last received webhook event
     *
     * Only exists to aid testing
     *
     * @var null|array
     */
    public $lastPayload;

    /**
     * Attach to specific events with the shared manager
     * @param  EventManagerInterface $events
     * @return void
     */
    public function attach(EventManagerInterface $events)
    {
        $shared = $events->getSharedManager();
        $this->listeners[] = $shared->attach('NetgluePrismic\Mvc\Controller\PrismicController', 'webhookAction', array($this, 'onWebhookReceive'));
    }

    /**
     * Callback on receive webhook
     * @param EventInterface $event
     * @return void
     */
    public function onWebhookReceive(EventInterface $event)
    {
        /**
         * Retrieve the API from the target controller and inject into ourself
         */
        $controller = $event->getTarget();
        $context = $controller->getContext();
        $this->setPrismicApi($context->getPrismicApi());

        /**
         * Respond to the event
         */
        $type = $event->getParam('type');

        $this->lastPayload = $event->getParams();

        if('api-update' === $type) {
            $this->clearCache();
        }
    }

    /**
     * Clear the API Cache
     * @return void
     */
    public function clearCache()
    {
        $api = $this->getPrismicApi();
        if(!$api) {
            throw new Exception\RuntimeException('Prismic API has not been injected');
        }
        $cache = $api->getCache();
        if($cache instanceof CacheInterface) {
            $cache->clear();
        }
    }

}
