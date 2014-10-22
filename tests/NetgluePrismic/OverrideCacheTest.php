<?php

namespace NetgluePrismic;

use Zend\Cache\StorageFactory;

use Zend\Mvc\Service\ServiceManagerConfig;
use Zend\ServiceManager\ServiceManager;

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
        $services = bootstrap::getServiceManager();
        $services->setAllowOverride(true);
    }

    public function testNullCache()
    {
        $services = bootstrap::getServiceManager();
        $cache = $services->get('NetgluePrismic\Cache\Disable');
        $this->assertInstanceOf('Prismic\Cache\NoCache', $cache);
    }

    public function getServiceManager()
    {
        $services = bootstrap::getServiceManager();
        $serviceManager = new ServiceManager(new ServiceManagerConfig());
        $serviceManager->setAllowOverride(true);
        $serviceManager->setService('ApplicationConfig', $services->get('ApplicationConfig'));
        $serviceManager->get('ModuleManager')->loadModules();
        return $serviceManager;
    }

    public function testCacheOverriddenCorrectlyWhenConfigured()
    {
        $services = $this->getServiceManager();
        $config = $services->get('config');
        $config['prismic']['cache'] = 'NetgluePrismic\Cache\Disable';
        $services->setService('config', $config);
        $api = $services->get('Prismic\Api');
        $this->assertInstanceOf('Prismic\Cache\NoCache', $api->getCache());
    }

    public function testFacadeIsInstantiatedWhenCacheIsZendStorage()
    {
        // set config to use a different cache factory
        $services = $this->getServiceManager();
        $config = $services->get('config');
        $config['prismic']['cache'] = 'CustomCache';
        $services->setService('config', $config);

        $services->setService('CustomCache', $this->storage);

        $api = $services->get('Prismic\Api');

        $this->assertInstanceOf('NetgluePrismic\Cache\Facade', $api->getCache());
        $this->assertSame($this->storage, $api->getCache()->getStorage());
    }

}
