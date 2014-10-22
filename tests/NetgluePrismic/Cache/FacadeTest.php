<?php

namespace NetgluePrismic\Cache;

use Zend\Cache\StorageFactory;

class FacadeTest extends \PHPUnit_Framework_TestCase
{

    private $cache;

    public function setUp()
    {
        $this->cache = StorageFactory::factory(
            array(
                'adapter' => 'apc',
                'options' => array(

                ),
            )
        );
    }

    public function testCreateInstance()
    {
        $facade = new Facade($this->cache);
        $this->assertSame($this->cache, $facade->getStorage());

        return $facade;
    }

    /**
     * @depends testCreateInstance
     */
    public function testBasicOperations(Facade $cache)
    {
        $value = array(1,2,3);
        $key = 'prismic_cache_test';

        $this->assertNull($cache->get($key));
        $this->assertTrue($cache->set($key, $value));
        $this->assertSame($value, $cache->get($key));
        $this->assertTrue($cache->delete($key));
        $this->assertNull($cache->get($key));

        $cache->set($key, $value);
        $this->assertSame($value, $cache->get($key));
        $this->assertTrue($cache->clear());
        $this->assertNull($cache->get($key));

    }

}
