<?php

return array(

    'prismic' => array(
        //"api" => "https://example.prismic.io/api",
        //"token" => 'Some Permanent Token',
        //"clientId" => 'Some Client ID',
        //"clientSecret" => 'Some Client Secret',

        /**
         * Webhooks will only work if you first setup the webhook at your repository
         * with the correct url and add a secret to be sent with the JSON payload
         * then add that secret here:
         */
        //"webhookSecret" => 'SecretPassword',

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

        /**
         * Configuration for the Head meta view helper for prismic documents
         *
         * The idea is that you would consistently name properties of documents in the masks you
         * define that correspond to common html head meta values/props. The view helper scans the document
         * for these named properties and if present, sets the appropriate head property using the other standard
         * ZF view helpers such as headTitle() headMeta() etc.
         */
        'HeadMetaListener' => array(
            'enabled' => false,
            'propertyMap' => array(
                'title' => 'meta_title',
                'description' => 'meta_description',
                'ogImage' => 'og_image',
                'ogTitle' => 'meta_title',
                'ogDescription' => 'meta_description',
            ),
        ),

    ),

    /**
     * These are the ready working routes out of the box
     */
    'router' => array(
        'routes' => array(
            // Oauth initiation
            'prismic-signin' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/prismic-signin',
                    'defaults' => array(
                        'controller' => 'NetgluePrismic\Mvc\Controller\PrismicController',
                        'action' => 'signin',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    // Callback for exchanging the code with a temporary access token
                    'callback' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '/callback',
                            'defaults' => array(
                                'action' => 'oauth-callback',
                            ),
                        ),
                    ),
                ),
            ),
            // Change the ref used for content displayed on the site for authenticated users, or open-content plans
            'prismic-ref' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/change-repository-ref',
                    'defaults' => array(
                        'controller' => 'NetgluePrismic\Mvc\Controller\PrismicController',
                        'action' => 'change-ref',
                    ),
                ),
            ),
            // Recieve notifications from Prismic.io when the content is updated etc.
            'prismic-webhook' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/prismic-webhook',
                    'defaults' => array(
                        'controller' => 'NetgluePrismic\Mvc\Controller\PrismicController',
                        'action' => 'webhook',
                    ),
                ),
            ),
        ),
    ),

    // Only used for the toolbar
    'view_manager' => array(
        'template_path_stack' => array(
            'netglue-prismic' => __DIR__ . '/../view',
        ),
    ),
);
