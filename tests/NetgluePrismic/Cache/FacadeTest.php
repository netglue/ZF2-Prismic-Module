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
        $this->assertFalse($cache->has($key));
        $this->assertNull($cache->set($key, $value));
        $this->assertTrue($cache->has($key));
        $this->assertSame($value, $cache->get($key));
        $this->assertNull($cache->delete($key));
        $this->assertNull($cache->get($key));

        $cache->set($key, $value);
        $this->assertSame($value, $cache->get($key));
        $this->assertNull($cache->clear());
        $this->assertNull($cache->get($key));

    }

    /**
     * @depends testCreateInstance
     */
    public function testNormalizeKeyReturnsUnmodifiedKey(Facade $cache)
    {
        $key = str_repeat('a', 251);
        $this->assertSame($key, $cache->normalizeKey($key));
    }

    public function testMemcachedStorageNormalizesKey()
    {
        $adapter = StorageFactory::factory(
            array(
                'adapter' => 'memcached',
                'options' => array(
                    'ttl' => 2 * 60 * 60,
                    'servers' => array(
                        array(
                            'host' => 'localhost',
                            'port' => 11211,
                        ),
                    ),
                ),
            )
        );
        $facade = new Facade($adapter);

        $key = str_repeat('http://www.foo.bar.com/Blah?=foo&baz=bat', 20);
        $expect = md5($key);

        $this->assertSame($expect, $facade->normalizeKey($key));


    }

}
