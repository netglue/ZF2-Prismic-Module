<?php

namespace NetgluePrismic\Mvc\Listener;

use NetgluePrismic\bootstrap;

class ToolbarListenerTest extends \Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase
{

    public function setUp()
    {
        $this->setApplicationConfig(include __DIR__ . '/../../../TestConfig.php.dist');
        parent::setUp();
    }

    public function testGetInstance()
    {
        $services = $this->getApplicationServiceLocator();
        $toolbar = $services->get('NetgluePrismic\Mvc\Listener\ToolbarListener');
        $this->assertInstanceOf('NetgluePrismic\Mvc\Listener\ToolbarListener', $toolbar);
        return $toolbar;
    }

    /**
     * @depends testGetInstance
     */
    public function testRenderingDisabledByDefault(ToolbarListener $toolbar)
    {
        $this->assertFalse($toolbar->shouldRender());
    }

    /**
     * @depends testGetInstance
     */
    public function testShouldRender(ToolbarListener $toolbar)
    {
        $toolbar->setShouldRender(true);
        $this->assertTrue($toolbar->shouldRender());
    }

    public function configureEvent()
    {
        $event = new \Zend\Mvc\MvcEvent;
        $event->setApplication($this->getApplication());
        return $event;
    }

    /**
     * @depends testGetInstance
     */
    public function testToolbarIsRendered(ToolbarListener $toolbar)
    {
        $event = $this->configureEvent();
        $response = $this->getApplication()->getResponse();

        $markup = '<html><head></head><body></body></html>';
        $response->setContent($markup);

        $this->assertEquals($markup, $response->getContent());

        $toolbar->injectToolbar($event);
        $this->assertNotEquals($markup, $response->getContent());
    }

    /**
     * @depends testGetInstance
     */
    public function testToolbarIsNotRenderedWhenDisabled(ToolbarListener $toolbar)
    {
        $event = $this->configureEvent();
        $response = $this->getApplication()->getResponse();

        $markup = '<html><head></head><body></body></html>';
        $response->setContent($markup);

        $toolbar->setShouldRender(false);

        $toolbar->injectToolbar($event);
        $this->assertEquals($markup, $response->getContent());
    }

}
