<?php

namespace NetgluePrismic\Mvc\Listener;

class SelectedRefListenerTest extends \Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase
{

    public function setUp()
    {
        $this->setApplicationConfig(include __DIR__ . '/../../../TestConfig.php.dist');
        parent::setUp();
    }

    public function testGetInstance()
    {
        $services = $this->getApplicationServiceLocator();
        $listener = $services->get('NetgluePrismic\Mvc\Listener\SelectedRefListener');
        $this->assertInstanceOf('NetgluePrismic\Mvc\Listener\SelectedRefListener', $listener);

        return $listener;
    }

    /**
     * @depends testGetInstance
     */
    public function testGetSessionRefIsNull(SelectedRefListener $listener)
    {
        $this->assertNull($listener->getSessionRef());

        $new = new SelectedRefListener;
        $this->assertNull($new->getSessionRef());
    }

    /**
     * @depends testGetInstance
     */
    public function testGetSessionRef(SelectedRefListener $listener)
    {
        $services = $this->getApplicationServiceLocator();
        $session = $services->get('NetgluePrismic\Session\PrismicContainer');
        $session->setRef('foo');
        $this->assertSame('foo', $listener->getSessionRef());
    }

    /**
     * @depends testGetInstance
     */
    public function testGetCookieRefIsNull(SelectedRefListener $listener)
    {
        $app = $this->getApplication();
        $event = $app->getMvcEvent();
        $this->assertNull($listener->getCookieRef($event));
    }

    /**
     * @depends testGetInstance
     */
    public function testGetCookieRef(SelectedRefListener $listener)
    {
        $name = str_replace('.', '_', \Prismic\Api::PREVIEW_COOKIE);
        $_COOKIE[$name] = 'cookie';
        $this->getRequest()->setCookies($_COOKIE);
        $app = $this->getApplication();
        $event = $app->getMvcEvent();
        $this->assertSame('cookie', $listener->getCookieRef($event));
    }

    /**
     * @depends testGetInstance
     */
    public function testSetPreviewRef(SelectedRefListener $listener)
    {
        //$this->reset();
        $services = $this->getApplicationServiceLocator();
        $context = $listener->getContext();
        $session = $services->get('NetgluePrismic\Session\PrismicContainer');
        $app = $this->getApplication();
        $event = $app->getMvcEvent();

        // Context should end up with the session ref
        $session->setRef('session');
        $this->assertSame('session', $listener->getSessionRef());
        $this->assertNull($listener->getCookieRef($event));

        $listener->setPreviewRef($event);
        $this->assertSame('session', $context->getRefAsString());



        $name = str_replace('.', '_', \Prismic\Api::PREVIEW_COOKIE);
        $_COOKIE[$name] = 'cookie';
        $this->getRequest()->setCookies($_COOKIE);

        $listener->setPreviewRef($event);
        $this->assertSame('cookie', $context->getRefAsString());


    }

}
