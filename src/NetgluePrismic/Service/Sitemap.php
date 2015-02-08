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
use Prismic\SearchForm;
use Prismic\Predicates;
use Prismic\Response;

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
     * @param LinkResolver  $resolver
     * @return void
     */
    public function setLinkResolver(LinkResolver $resolver)
    {
        $this->linkResolver = $resolver;
    }

    /**
     * @param LinkGenerator $generator
     * @return void
     */
    public function setLinkGenerator(LinkGenerator $generator)
    {
        $this->linkGenerator = $generator;
    }

    public function setConfig(array $config)
    {
        $this->config = $config;
    }

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
        foreach($this->config as $sitemap) {
            if(isset($sitemap['name'])) {
                $names[] = $sitemap['name'];
            }
        }
        return $names;
    }
    
    public function getConfigByContainerName($name)
    {
        $config = null;
        foreach($this->config as $data) {
            if(isset($data['name']) && $data['name'] === $name) {
                $config = $data;
            }
        }
        return $config;
    }

    public function getContainerByName($name)
    {
        /**
         * Try to retrieve the generator from the cache. If it can't be found, create it
         */
        if(array_key_exists($name, $this->containers)) {
            return $this->containers[$name];
        }
        $cacheKey = sprintf('NetgluePrismicSitemapContainer-%s', $name);
        if($this->cache && $this->cache->hasItem($cacheKey)) {
            $success = false;
            $container = $this->cache->getItem($cacheKey, $success);
            if(true === $success) {
                $this->containers[$name] = $container;
                return $container;
            }
        }
        
        $config = $this->getConfigByContainerName($name);
        if(null === $config) {
            return null;
        }
        
        /**
         * If documentTypes has not been set, we'll be retrieving everything
         */
        $documentTypes = isset($config['documentTypes']) ? $config['documentTypes'] : array();
        
        if(!isset($config['propertyMap'])) {
            throw new Exception\RuntimeException(sprintf(
                'No mapping of sitemap property to prismic document fragment has been provided for the container named %s',
                $name));
        }
        
        $generator = $this->generatorFactory($name, $documentTypes, $config['propertyMap']);
        $container = $generator->getContainer();
        if($container instanceof Container) {
            $this->containers[$name] = $container;
            if($this->cache) {
                $this->cache->setItem($cacheKey, $container);
            }
            
            return $container;
        }
        
        return null;
    }

    protected function generatorFactory($name, $documentTypes, $propertyMap)
    {
        $generator = new SitemapGenerator;
        $generator->setContext($this->getContext());
        $generator->setPrismicApi($this->getPrismicApi());
        $generator->setDocumentTypes($documentTypes);
        $generator->setLinkResolver($this->linkResolver);
        $generator->setLinkGenerator($this->linkGenerator);
        $generator->setPropertyMap($propertyMap);
        $generator->setName($name);

        return $generator;
    }

}
