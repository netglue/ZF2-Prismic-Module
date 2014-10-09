<?php
return array(
    'invokables' => array(
        'NetgluePrismic\View\Helper\EditAtPrismic' => 'NetgluePrismic\View\Helper\EditAtPrismic',
    ),
    'factories' => array(
        'NetgluePrismic\View\Helper\Prismic' => 'NetgluePrismic\View\Service\PrismicViewHelperFactory',
    ),
    'aliases' => array(
        'prismic' => 'NetgluePrismic\View\Helper\Prismic',
        'editAtPrismic' => 'NetgluePrismic\View\Helper\EditAtPrismic',
    ),
);
