<?php
namespace NetgluePrismic\Factory;

use NetgluePrismic\bootstrap;

use GuzzleHttp\Client as HttpAdapter;

use Zend\Mvc\Service\ServiceManagerConfig;
use Zend\ServiceManager\ServiceManager;

use Zend\Session\Container;

class ApiFactoryTest extends \PHPUnit_Framework_TestCase
{

    public function getServiceManager()
    {
        $services = bootstrap::getServiceManager();
        $serviceManager = new ServiceManager(new ServiceManagerConfig());
        $serviceManager->setAllowOverride(true);
        $serviceManager->setService('ApplicationConfig', $services->get('ApplicationConfig'));
        $serviceManager->get('ModuleManager')->loadModules();
        return $serviceManager;
    }

    public function testOverrideHttpClient()
    {
        // set config to use an http client factory
        $services = $this->getServiceManager();
        $config = $services->get('config');
        $config['prismic']['httpClient'] = 'CustomClient';
        $services->setService('config', $config);

        $http = new HttpAdapter;

        $services->setService('CustomClient', $http);

        $api = $services->get('Prismic\Api');

        $this->assertInstanceOf(HttpAdapter::class, $api->getHttpClient());
        $this->assertSame($http, $api->getHttpClient());
    }


    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage No configuration has been provided
     */
    public function testCreateServiceThrowsExceptionForNoApiConfig()
    {
        $services = $this->getServiceManager();
        $config = $services->get('config');
        $config['prismic']['api'] = null;
        $services->setService('config', $config);
        try {
            $services->get('Prismic\Api');
            $this->fail();
        } catch(\Zend\ServiceManager\Exception\ExceptionInterface $e) {
            throw $e->getPrevious();
        }
    }

    public function testInvalidSessionTokenWillReturnDefaultApi()
    {
        $session = new Container('Prismic');
        $session->access_token = 'Duff Token';
        $services = $this->getServiceManager();
        $api = $services->get('Prismic\Api');
        $this->assertInstanceOf('Prismic\Api', $api);
        // I can only figure out if the correct access token was sent if I mock the http client
        // So, this test currently is currently incomplete
        $this->markTestIncomplete();
    }



}
