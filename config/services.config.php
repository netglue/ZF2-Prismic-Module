<?php

return array(

    'factories' => array(
        // Prismic SDK Api \Prismic\Api
        'Prismic\Api' => 'NetgluePrismic\Factory\PrismicApiClientFactory',
        // Site-wide context \NetgluePrismic\Context
        'Prismic\Context' => 'NetgluePrismic\Factory\ContextFactory',

        // Options for the router/link resolver
        'NetgluePrismic\Mvc\Router\RouterOptions' => 'NetgluePrismic\Mvc\Service\RouterOptionsFactory',

        // Link Resolver
        'NetgluePrismic\Mvc\LinkResolver' => 'NetgluePrismic\Mvc\Service\LinkResolverFactory',

        // Session for storing access tokens and selected ref/release
        'NetgluePrismic\Session\PrismicContainer' => 'NetgluePrismic\Session\ContainerFactory',

        /**
         * Listeners
         */

        // Automatically set meta title etc when successfully routed to a single document
        'NetgluePrismic\Mvc\Listener\HeadMetaListener' => 'NetgluePrismic\Mvc\Service\HeadMetaListenerFactory',

        // Injects the routed document into the view helper
        'NetgluePrismic\Mvc\Listener\ViewHelperDocumentListener' => 'NetgluePrismic\Mvc\Service\ViewHelperDocumentListenerFactory',

        // Listener to inject the toolbar
        'NetgluePrismic\Listener\ToolbarListener' => function($sm) {
            return new \NetgluePrismic\Listener\ToolbarListener($sm->get('ViewRenderer'), $sm);
        }
    ),

    'aliases' => array(
        'PrismicApiClient' => 'Prismic\Api',
        'PrismicRouterOptions' => 'NetgluePrismic\Mvc\Router\RouterOptions',
    ),

);
