<?php

return array(

    'factories' => array(
        'Prismic\Api' => 'NetgluePrismic\Factory\PrismicApiClientFactory',
        'Prismic\Context' => 'NetgluePrismic\Factory\ContextFactory',
    ),

    'aliases' => array(
        'PrismicApiClient' => 'Prismic\Api',
    ),

);
