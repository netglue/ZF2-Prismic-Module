<?php

return array(

    'factories' => array(
        'Prismic\Api' => 'NetgluePrismic\Factory\PrismicApiClientFactory',
    ),

    'aliases' => array(
        'PrismicApiClient' => 'Prismic\Api',
    ),

);
