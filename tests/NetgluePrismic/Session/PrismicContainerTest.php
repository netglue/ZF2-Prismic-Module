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
        $this->context = $services->get('Prismic\Context');
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

    public function testSessionHasMasterRefByDefaultWhenContextIsSet()
    {
        $container = new PrismicContainer('Prismic');
        $this->assertNull($container->ref);

        $container->setContext($this->context);
        $this->assertSame($this->context->getMasterRef()->getRef(), $container->ref);
    }

    public function testSetContextResetsRefToMasterForInvalidRef()
    {
        $container = new PrismicContainer('Prismic');
        $container->ref = 'Not a valid Ref';
        $this->assertEquals('Not a valid Ref', $container->ref);
        $container->setContext($this->context);
        $this->assertSame($this->context->getMasterRef()->getRef(), $container->ref);
    }

    public function testSetContextSetsContextRefWhenSessionRefIsValid()
    {
        $allRefs = $this->context->getPrismicApi()->refs();
        $notMaster = array_filter($allRefs, function ($value) {
            return !($value->isMasterRef());
        });
        if (!count($notMaster)) {
            $this->fail('There are no unpublised releases available in the prismic repository');
        }
        $notMaster = current($notMaster)->getRef();

        $container = new PrismicContainer('Prismic');
        $container->ref = $notMaster;

        $contextRef = $this->context->getRef();

        $this->assertNotEquals($container->ref, (string) $contextRef);
        $container->setContext($this->context);

        $this->assertSame($notMaster, $container->ref);
        $this->assertSame($container->ref, (string) $this->context->getRef());
        $this->assertNotSame($contextRef, (string) $this->context->getRef());
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
