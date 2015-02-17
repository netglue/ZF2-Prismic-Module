<?php

namespace NetgluePrismic\Session;

use NetgluePrismic\bootstrap;

class PrismicContainerTest extends \PHPUnit_Framework_TestCase
{

    private static $manager;

    private $context;

    public function setUp()
    {
        $_SESSION = array();
        \Zend\Session\Container::setDefaultManager(null);
        $config = new \Zend\Session\Config\StandardConfig(array(
            'storage' => 'Zend\\Session\\Storage\\ArrayStorage',
        ));
        $sessionManager = new \Zend\Session\SessionManager($config);
        $sessionManager->start();
        \Zend\Session\Container::setDefaultManager($sessionManager);
        self::$manager = $sessionManager;

        $services = bootstrap::getServiceManager();
        $this->context = $services->get('NetgluePrismic\Context');
    }

    public function tearDown()
    {
        $_SESSION = array();
        \Zend\Session\Container::setDefaultManager(null);

        $this->context->setRef($this->context->getMasterRef());
    }

    public function getContainer()
    {
        $services = bootstrap::getServiceManager();

        return $services->get('NetgluePrismic\Session\PrismicContainer');
    }

    public function testGetContainerFromServiceHasContext()
    {
        $container = $this->getContainer();
        $this->assertInstanceOf('NetgluePrismic\Session\PrismicContainer', $container);
        $this->assertInstanceOf('NetgluePrismic\Context', $container->getContext());
    }

    public function testSetGetRef()
    {
        $container = $this->getContainer();
        $this->assertNull($container->getRef());
        $container->setRef('foo');
        $this->assertSame('foo', $container->getRef());
    }

    public function testSetGetHasAccessToken()
    {
        $session = new PrismicContainer('Test');
        $this->assertFalse($session->hasAccessToken());
        $this->assertNull($session->getAccessToken());
        $session->setAccessToken('foo');
        $this->assertTrue($session->hasAccessToken());
        $this->assertSame('foo', $session->getAccessToken());
    }

}
