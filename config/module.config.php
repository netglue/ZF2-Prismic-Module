<?php

return array(

    'prismic' => array(

        /**
         * Basic configuration
         * As long as you provide the following, you should be good to go:
         *
         * You'll find the tokens, client ID etc in your repository settings:
         */
        //"api" => "https://example.prismic.io/api",
        //"token" => 'Some Permanent Token',
        //"clientId" => 'Some Client ID',
        //"clientSecret" => 'Some Client Secret',

        /**
         * To override the HttpClient, it must implement \Ivory\HttpAdapter\HttpAdapterInterface
         * See more at https://github.com/egeloen/ivory-http-adapter
         *
         * The Prismic.io SDK requires this interface. The value of 'httpClient'
         * should be a string service name that we can retrieve from the service locator
         */

        'httpClient' => null,

        /**
         * You can override the Cache adapter used by specifying a service name
         *
         * The cache instance should be a regular zend cache storage instance or an instance that implements
         * Prismic\Cache\CacheInterface. The former gets wrapped in a facade so that Prismic's Cache Interface is
         * satisfied.
         *
         * You can easily disable the api cache by setting 'cache' to the service name
         * NetgluePrismic\Cache\Disable - This factory in Factory/NoCacheFactory simply
         * returns an instance of Prismic\Cache\NoCache - i.e.
         * 'cache' => 'NetgluePrismic\Cache\Disable',
         */
        'cache' => null,

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
            'uid'      => 'uid',
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
                'title'              => 'meta_title',
                'description'        => 'meta_description',
                'keywords'           => 'meta_keywords',
                'robots'             => 'meta_robots',
                'ogImage'            => 'og_image',
                'ogTitle'            => 'meta_title',
                'ogDescription'      => 'meta_description',
                'twitterCard'        => 'twitter_card',
                'twitterTitle'       => 'meta_title',
                'twitterDescription' => 'meta_description',
                'twitterImage'       => 'twitter_image',
            ),
        ),

        /**
         * Automatic XML Site Map generation works on the assumption that
         * you provide a list of document types you want included.
         * Sitemaps are split up into multiple documents with a sitemap index file
         *
         * You would setup sitemap configuration for each type of document that
         * uses different fragment names for the relevant xml
         * By default, you'll end up with an empty sitemap index file at the relevant url.
         *
         * It's important to note that the default url as set in the config below
         * for the sitemap index file is /prismic-sitemap.xml
         * It's expected that you'd change this to the usual /sitemap.xml but it's set this way
         * so as to not conflict with any existing data.
         *
         */

        /*
        'sitemaps' => array(
            'sitemaps' => array(
                array(
                    'name' => 'blog-posts',
                    'documentTypes' => array(
                        'blog-posting',
                    ),
                    'propertyMap' => array(
                        'changefreq' => 'my_change_freq_fragment_name',
                        'lastmod'    => 'not-much-use-yet',
                        'priority'   => 'my-priority',
                    ),
                ),
            ),
        ),
        */
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
            'prismic-preview' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/preview-document',
                    'defaults' => array(
                        'controller' => 'NetgluePrismic\Mvc\Controller\PrismicController',
                        'action' => 'preview',
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
        'template_map' => array(
            'netglue-prismic/sitemap/index'   => __DIR__ . '/../view/netglue-prismic/sitemap/index.phtml',
            'netglue-prismic/sitemap/sitemap' => __DIR__ . '/../view/netglue-prismic/sitemap/sitemap.phtml',
        ),
    ),
);
