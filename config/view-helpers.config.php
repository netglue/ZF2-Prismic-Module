<?php
return array(
    'invokables' => array(
        'NetgluePrismic\View\Helper\EditAtPrismic' => 'NetgluePrismic\View\Helper\EditAtPrismic',
    ),
    'factories' => array(
        'NetgluePrismic\View\Helper\PrismicDocumentHead' => 'NetgluePrismic\View\Service\PrismicDocumentHeadFactory',
        'NetgluePrismic\View\Helper\Prismic' => 'NetgluePrismic\View\Service\PrismicViewHelperFactory',
    ),
    'aliases' => array(
        'prismicDocumentHead' => 'NetgluePrismic\View\Helper\PrismicDocumentHead',
        'prismic' => 'NetgluePrismic\View\Helper\Prismic',
        'editAtPrismic' => 'NetgluePrismic\View\Helper\EditAtPrismic',
    ),
);
