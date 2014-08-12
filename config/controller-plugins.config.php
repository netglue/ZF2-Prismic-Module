<?php

return array(

    'factories' => array(
        'NetgluePrismic\Mvc\Controller\Plugin\Prismic' => 'NetgluePrismic\Mvc\Service\PrismicControllerPluginFactory',
    ),

    'aliases' => array(
        'Prismic' => 'NetgluePrismic\Mvc\Controller\Plugin\Prismic',
    ),

);
