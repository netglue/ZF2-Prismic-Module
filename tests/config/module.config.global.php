<?php

return array(

    'prismic' => array(
        "api" => "https://zf2-module.prismic.io/api",
        "token" => NULL,
        "clientId" => NULL,
        "clientSecret" => NULL,
    ),

    'view_manager' => array(
        'not_found_template' => 'error/404',
        'exception_template' => 'error/500',
        'template_map' => array(
            'error/404' => __DIR__ . '/../data/dummy-error-template.phtml',
            'error/500' => __DIR__ . '/../data/dummy-error-template.phtml',
            'layout/layout' => __DIR__ . '/../data/dummy-error-template.phtml',
        ),
    ),

    'router' => array(
        'routes' => array(
            'test-bookmark' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/test-bookmark',
                    'defaults' => array(
                        'controller' => 'NetgluePrismic\TestController',
                        'action' => 'bookmarkAction',
                        'bookmark' => 'unit-test-bookmark',
                    ),
                ),
            ),
            'test-mask' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/masked/:slug/:prismic-id',
                    'defaults' => array(
                        'controller' => 'NetgluePrismic\TestController',
                        'action' => 'maskAction',
                        'mask' => 'test',
                    ),
                ),
            ),
        ),
    ),
);