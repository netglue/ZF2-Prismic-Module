<?php

namespace NetgluePrismic\Service;

use NetgluePrismic\bootstrap;
use Zend\Cache\StorageFactory;

class SitemapTest extends \PHPUnit_Framework_TestCase
{

    protected $sitemap;

    protected $cache;

    public function setup()
    {
        $sl = bootstrap::getServiceManager();
        $this->sitemap = $sl->get('NetgluePrismic\Service\Sitemap');
        $this->cache = StorageFactory::factory(
            array(
                'adapter' => 'apc',
                'options' => array(

                ),
            )
        );
        $this->cache->flush();
    }

    public function testInstance()
    {
        $sl = bootstrap::getServiceManager();
        $service = $sl->get('NetgluePrismic\Service\Sitemap');
        $this->assertInstanceOf('NetgluePrismic\Service\Sitemap', $service);

        return $service;
    }

    /**
     * @depends testInstance
     */
    public function testGetSitemapNamesReturnsArray(Sitemap $service)
    {
        $this->assertInternalType('array', $service->getSitemapNames());
        $this->assertCount(3, $service->getSitemapNames());
        $this->assertContains('test', $service->getSitemapNames());
        $this->assertContains('no-results', $service->getSitemapNames());

        return $service;
    }

    /**
     * @depends testInstance
     */
    public function testGetConfigByContainerNameReturnsExpectedConfig(Sitemap $service)
    {
        $config = $service->getConfigByContainerName('test');
        $this->assertInternalType('array', $config);
        $this->assertArrayHasKey('name', $config);
        $this->assertArrayHasKey('propertyMap', $config);
        $this->assertArrayHasKey('documentTypes', $config);
    }

    /**
     * @depends testInstance
     */
    public function testGetConfigByContainerNameReturnsNullForInvalidName(Sitemap $service)
    {
        $config = $service->getConfigByContainerName('unknown');
        $this->assertNull($config);
    }

    /**
     * @depends testInstance
     */
    public function testGetContainerByNameReturnsNullForInvalidName(Sitemap $service)
    {
        $container = $service->getContainerByName('unknown');
        $this->assertNull($container);
    }

    /**
     * @depends testInstance
     */
    public function testGetContainerByNameReturnsEmptyContainer(Sitemap $service)
    {
        $container = $service->getContainerByName('no-results');
        $this->assertInstanceOf('Zend\Navigation\Navigation', $container);
        $this->assertEquals(0, $container->count());
    }

    /**
     * @depends testInstance
     * @expectedException NetgluePrismic\Exception\RuntimeException
     * @expectedExceptionMessage No mapping of sitemap property to prismic document fragment
     */
    public function testExceptionIsThrownWhenCreatingContainerWithoutPropertyMap(Sitemap $service)
    {
        $container = $service->getContainerByName('no-property-map');
    }

    /**
     * @depends testInstance
     */
    public function testGetContainerByNameReturnsSameContainer(Sitemap $service)
    {
        $container = $service->getContainerByName('test');
        $this->assertSame($container, $service->getContainerByName('test'));
    }

    /**
     * @depends testInstance
     */
    public function testGetSetCachePrefix(Sitemap $service)
    {
        $this->assertNull($service->getCachePrefix(), 'Cache prefix should be initially null');
        $service->setCachePrefix('test');
        $this->assertSame('test', $service->getCachePrefix(), 'Cache prefix should be set with a string');
        $service->setCachePrefix('');
        $this->assertNull($service->getCachePrefix(), 'Empty value should nullify cache prefix');

        return $service;
    }

    /**
     * @depends testGetSetCachePrefix
     */
    public function testGetCacheKeyForContainerNameIncludesPrefix(Sitemap $service)
    {
        $this->assertNull($service->getCachePrefix());
        $this->assertSame('NetgluePrismicSitemapContainer-test', $service->getCacheKeyForContainerName('test'));
        $service->setCachePrefix('test');
        $this->assertSame('test-NetgluePrismicSitemapContainer-test', $service->getCacheKeyForContainerName('test'));
        $service->setCachePrefix('');
        $this->assertNull($service->getCachePrefix());
    }

    /**
     * @depends testInstance
     */
    public function testContainersAreCached(Sitemap $service)
    {
        $service->setCache($this->cache);
        $service->resetContainers();
        $key = $service->getCacheKeyForContainerName('test');
        $this->assertFalse($this->cache->hasItem($key));
        $container = $service->getContainerByName('test');
        $this->assertTrue($this->cache->hasItem($key));

        $service->resetContainers();
        $cached = $this->cache->getItem($key);
        $container = $service->getContainerByName('test');
        $this->assertSame($container->toArray(), $cached->toArray());

    }
}
