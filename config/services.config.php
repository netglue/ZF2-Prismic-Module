<?php

return array(

    'factories' => array(
        'Prismic\Api' => 'NetgluePrismic\Factory\PrismicApiClientFactory',
        'Prismic\Context' => 'NetgluePrismic\Factory\ContextFactory',
        'NetgluePrismic\Mvc\Router\RouterOptions' => 'NetgluePrismic\Mvc\Service\RouterOptionsFactory',
        'NetgluePrismic\Mvc\LinkResolver' => 'NetgluePrismic\Mvc\Service\LinkResolverFactory',
    ),

    'aliases' => array(
        'PrismicApiClient' => 'Prismic\Api',
        'PrismicRouterOptions' => 'NetgluePrismic\Mvc\Router\RouterOptions',
    ),

);
