<?php

namespace NetgluePrismic\Service;

use NetgluePrismic\ContextAwareInterface;
use NetgluePrismic\ContextAwareTrait;
use NetgluePrismic\ApiAwareInterface;
use NetgluePrismic\ApiAwareTrait;
use NetgluePrismic\Mvc\LinkResolver;
use NetgluePrismic\Mvc\LinkGenerator;

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


    public function getGeneratorByName($name)
    {
        /**
         * Try to retrieve the generator from the cache. If it can't be found, create it
         */
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
