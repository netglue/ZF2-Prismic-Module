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
);
