<?php

return array(

    'prismic' => array(
        //"api" => "https://example.prismic.io/api",
        //"token" => 'Some Permanent Token',
        //"clientId" => 'Some Client ID',
        //"clientSecret" => 'Some Client Secret',

        /**
         * You can override the HTTP Client and the Cache adapter used by specifying a service name
         * to retrieve during initialisation.
         * The HTTP Client should implement \Guzzle\Http\ClientInterface
         *
         * The cache instance should be a regular zend cache storage instance or an instance that implements
         * Prismic\Cache\CacheInterface. The former gets wrapped in a facade so that Prismic's Cache Interface is
         * satisfied.
         */
        'httpClient' => null,
        'cache' => null,

        /**
         * You can easily disable the api cache by setting 'cache' to the service name
         * NetgluePrismic\Cache\Disable - This factory in Factory/NoCacheFactory simply
         * returns an instance of Prismic\Cache\NoCache
         */
        // 'cache' => 'NetgluePrismic\Cache\Disable',


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

        /**
         * Automatic XML Site Map generation works on the assumption that
         * you provide a list of document types you want included.
         * Sitemaps are split up into multiple documents with a sitemap index file
         *
         * You would setup sitemap configuration for each type of document that
         * uses different fragment names for the relevant xml
         */
        'sitemaps' => array(
            'cache' => null,
            'sitemaps' => array(
                'portfolio' => array(
                    'name' => 'portfolio',
                    'documentTypes' => array(
                        'website',
                    ),
                    'propertyMap' => array(
                        'changefreq' => 'my_change_freq_fragment_name',
                        'lastmod' => 'not-much-use-yet',
                        'priority' => 'my-priority',
                    ),
                ),
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
            // Render XML Sitemaps
            'prismic-sitemap' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/prismic-sitemap.xml',
                    'defaults' => array(
                        'controller' => 'NetgluePrismic\Mvc\Controller\SitemapController',
                        'action' => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'container' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '/[:name].xml',
                            'defaults' => array(
                                'action' => 'sitemap',
                            ),
                        ),
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
