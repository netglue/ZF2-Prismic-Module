<?php

namespace NetgluePrismic\Service;

use NetgluePrismic\ContextAwareInterface;
use NetgluePrismic\ContextAwareTrait;
use NetgluePrismic\ApiAwareInterface;
use NetgluePrismic\ApiAwareTrait;
use NetgluePrismic\Mvc\LinkResolver;
use NetgluePrismic\Mvc\LinkGenerator;

use NetgluePrismic\Exception;

use Prismic\Document;

use Zend\Navigation\Navigation as Container;
use Zend\Cache\Storage\StorageInterface as Cache;

class Sitemap implements ContextAwareInterface,
                               ApiAwareInterface
{

    use ContextAwareTrait,
        ApiAwareTrait;

    /**
     * Cache for storing the containers
     */
    private $cache;

    /**
     * Sitemap Configuration passed to individual generators
     */
    private $config;

    /**
     * Array of containers where each container signifies an individual sitemap
     * @var array
     */
    private $containers = array();

    /**
     * @var LinkResolver
     */
    protected $linkResolver;

    /**
     * @var LinkGenerator
     */
    protected $linkGenerator;

    /**
     * @param  LinkResolver $resolver
     * @return void
     */
    public function setLinkResolver(LinkResolver $resolver)
    {
        $this->linkResolver = $resolver;
    }

    /**
     * @param  LinkGenerator $generator
     * @return void
     */
    public function setLinkGenerator(LinkGenerator $generator)
    {
        $this->linkGenerator = $generator;
    }

    /**
     * Set configuration of sitemaps
     *
     * The array is expected to take the form:
     *
     * [
     *     [
     *          'name'          => 'some-name',
     *          'documentTypes' => [],
     *          'propertyMap'   => [],
     *     ],
     *     // More Sitemaps...
     * ]
     *
     * @param  array $config
     * @return void
     */
    public function setConfig(array $config)
    {
        $this->config = $config;
    }

    /**
     * Set a storage interface to cache containers with
     * @param  Cache $cache
     * @return void
     */
    public function setCache(Cache $cache = null)
    {
        $this->cache = $cache;
    }

    /**
     * Return an array of sitemap names so they can be used to construct an index
     * @return array
     */
    public function getSitemapNames()
    {
        $names = array();
        foreach ($this->config as $sitemap) {
            if (isset($sitemap['name'])) {
                $names[] = $sitemap['name'];
            }
        }

        return $names;
    }

    /**
     * Reset the containers so that tey are built again
     * @return void
     */
    public function resetContainers()
    {
        $this->containers = array();
    }

    /**
     * Return the configuration of the nav container with the given name
     * @param  string     $name The container name
     * @return array|null An array of configuation or null if the container config cannot be found
     */
    public function getConfigByContainerName($name)
    {
        $config = null;
        foreach ($this->config as $data) {
            if (isset($data['name']) && $data['name'] === $name) {
                $config = $data;
            }
        }

        return $config;
    }

    /**
     * Return the cache key for the given container name
     * @param  string $name
     * @return string
     */
    public function getCacheKeyForContainerName($name)
    {
        return sprintf('NetgluePrismicSitemapContainer-%s', $name);
    }

    /**
     * Return a Zend Navigation Container suitable for use by Sitemap View Helpers
     * @param  string         $name The name of the configured container
     * @return Container|null
     */
    public function getContainerByName($name)
    {
        /**
         * Try to retrieve the generator from the cache. If it can't be found, create it
         */
        if (array_key_exists($name, $this->containers)) {
            return $this->containers[$name];
        }
        $cacheKey = $this->getCacheKeyForContainerName($name);
        if ($this->cache && $this->cache->hasItem($cacheKey)) {
            $success = false;
            $container = $this->cache->getItem($cacheKey, $success);
            if (true === $success) {
                $this->containers[$name] = $container;

                return $container;
            }
        }

        $config = $this->getConfigByContainerName($name);
        if (null === $config) {
            return null;
        }

        /**
         * If documentTypes has not been set, we'll be retrieving everything
         */
        $documentTypes = isset($config['documentTypes']) ? $config['documentTypes'] : array();

        if (!isset($config['propertyMap'])) {
            throw new Exception\RuntimeException(sprintf(
                'No mapping of sitemap property to prismic document fragment has been provided for the container named %s',
                $name));
        }

        $generator = $this->generatorFactory($documentTypes, $config['propertyMap']);
        $container = $generator->getContainer();
        if ($container instanceof Container) {
            $this->containers[$name] = $container;
            if ($this->cache) {
                $this->cache->setItem($cacheKey, $container);
            }

            return $container;
        }

        return null;
    }

    /**
     * Create a Sitemap Generator
     * @param  array            $documentTypes
     * @param  array            $propertyMap
     * @return SitemapGenerator
     */
    protected function generatorFactory(array $documentTypes, array $propertyMap)
    {
        $generator = new SitemapGenerator;
        $generator->setContext($this->getContext());
        $generator->setPrismicApi($this->getPrismicApi());
        $generator->setDocumentTypes($documentTypes);
        $generator->setLinkResolver($this->linkResolver);
        $generator->setLinkGenerator($this->linkGenerator);
        $generator->setPropertyMap($propertyMap);

        return $generator;
    }

}
