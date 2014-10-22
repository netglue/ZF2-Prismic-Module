<?php

namespace NetgluePrismic;

use Zend\Cache\StorageFactory;


class OverrideCacheTest extends \PHPUnit_Framework_TestCase
{

    private $storage;

    public function setUp()
    {
        $this->storage = StorageFactory::factory(
            array(
                'adapter' => 'apc',
                'options' => array(

                ),
            )
        );
        $this->storage->flush();
    }

    public function testNullCache()
    {
        $services = bootstrap::getServiceManager();
        $cache = $services->get('NetgluePrismic\Cache\Disable');
        $this->assertInstanceOf('Prismic\Cache\NoCache', $cache);
    }

}
