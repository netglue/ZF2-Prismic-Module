<?php

return array(

    'prismic' => array(
        //"api" => "https://example.prismic.io/api",
        //"token" => 'Some Permanent Token',
        //"clientId" => 'Some Client ID',
        //"clientSecret" => 'Some Client Secret',

        /**
         * In order to resolve links to prismic documents within the application,
         * routes need to be setup that provide information about either the bookmark or
         * document type that is to be presented by the view.
         *
         * This config array tells us the names of the route parameters we'll be looking for when assembling
         * routes and determining which document to present at the route endpoint
         */
        'routeParameters' => array(
            'bookmark' => 'bookmark',
            'mask'     => 'mask',
            'ref'      => 'ref',
            'id'       => 'prismic-id',
            'slug'     => 'slug',
        ),

    ),

);
