<?php

namespace NetgluePrismic\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use NetgluePrismic\Service\Sitemap;
use NetgluePrismic\Cache;

class SitemapFactory implements FactoryInterface
{

    /**
     * Return Sitemap Service
     * @param  ServiceLocatorInterface $serviceLocator
     * @return Sitemap
     * @throws \RuntimeException
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('Config');
        $config = isset($config['prismic']['sitemaps']) ? $config['prismic']['sitemaps'] : array();

        $sitemap = new Sitemap(
            $serviceLocator->get(Cache::class)
        );

        /**
         * Cache
         */
        if(isset($config['cache_prefix'])) {
            $sitemap->setCachePrefix($config['cache_prefix']);
        }

        if(isset($config['exclude'])) {
            $sitemap->setExclusions($config['exclude']);
        }

        /**
         * API and Context
         */
        $sitemap->setPrismicApi($serviceLocator->get('Prismic\Api'));
        $sitemap->setContext($serviceLocator->get('NetgluePrismic\Context'));


        /**
         * Link Generation
         */
        $sitemap->setLinkResolver($serviceLocator->get('NetgluePrismic\Mvc\LinkResolver'));
        $sitemap->setLinkGenerator($serviceLocator->get('NetgluePrismic\Mvc\LinkGenerator'));

        /**
         * Sitemap Config
         */
        $mapConfig = isset($config['sitemaps']) ? $config['sitemaps'] : array();
        $sitemap->setConfig($mapConfig);

        return $sitemap;
    }

}
