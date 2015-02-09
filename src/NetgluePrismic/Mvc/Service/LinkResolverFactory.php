<?php

namespace NetgluePrismic\Mvc\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use NetgluePrismic\Mvc\LinkResolver;

class LinkResolverFactory implements FactoryInterface
{

    /**
     * Return Prismic routing options instance
     * @param  ServiceLocatorInterface $serviceLocator
     * @return LinkResolver
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $linkResolver = new LinkResolver;

        // A router is required to assemble urls
        $linkResolver->setRouter($serviceLocator->get('Router'));

        // Prismic Context and Api required for looking up bookmarks etc
        $linkResolver->setContext($serviceLocator->get('Prismic\Context'));

        // Router Options are used to identify Prismic sepcific variables when composing and deconstructing routes
        $routingOptions = $serviceLocator->get('NetgluePrismic\Mvc\Router\RouterOptions');
        $linkResolver->setRouterOptions($routingOptions);

        /**
         * Routes have to be set rather than querying the router for them because
         * it's a pain in the ass trying to get information from the many different types of
         * routers/stacks. There are no interface methods to interrogate default params for example
         */
        $config = $serviceLocator->get('Config');
        $linkResolver->setRoutes($config['router']['routes']);

        return $linkResolver;
    }

}
