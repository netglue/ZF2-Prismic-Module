<?php
return array(
    'invokables' => array(
        'NetgluePrismic\View\Helper\EditAtPrismic' => 'NetgluePrismic\View\Helper\EditAtPrismic',
    ),
    'factories' => array(
        'NetgluePrismic\View\Helper\PrismicDocumentHead' => 'NetgluePrismic\View\Service\PrismicDocumentHeadFactory',
    ),
    'aliases' => array(
        'prismicDocumentHead' => 'NetgluePrismic\View\Helper\PrismicDocumentHead',
        'editAtPrismic' => 'NetgluePrismic\View\Helper\EditAtPrismic',
    ),
);
