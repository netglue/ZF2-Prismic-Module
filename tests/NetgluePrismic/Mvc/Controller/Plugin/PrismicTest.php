<?php
namespace NetgluePrismic\Mvc\Controller\Plugin;

use NetgluePrismic\bootstrap;
use Prismic\Document;

class PrismicTest extends \PHPUnit_Framework_TestCase
{

    protected $mockParams;

    protected $plugin;

    public function setUp()
    {
        $services = bootstrap::getServiceManager();
        $manager = $services->get('ControllerPluginManager');
        $this->plugin = $manager->get('NetgluePrismic\Mvc\Controller\Plugin\Prismic');
    }

    public function testGetPlugin()
    {
        $this->assertInstanceOf('NetgluePrismic\Mvc\Controller\Plugin\Prismic', $this->plugin);
        return $this->plugin;
    }

    /**
     * @depends testGetPlugin
     */
    public function testSetGetController(Prismic $plugin)
    {
        $services = bootstrap::getServiceManager();
        $manager = $services->get('ControllerManager');
        $controller = $manager->get('NetgluePrismic\Mvc\Controller\PrismicController');
        $plugin->setController($controller);
        $this->assertSame($controller, $plugin->getController());
        return $plugin;
    }

    /**
     * @depends testSetGetController
     */
    public function testMockParams(Prismic $plugin)
    {
        $mockParams = $this->getMock('\Zend\Mvc\Controller\Plugin\Params');
        $mockParams->expects($this->any())
            ->method('fromRoute')
            ->will($this->returnValue('unit-test-bookmark'));

        $this->mockParams = $mockParams;

        $services = bootstrap::getServiceManager();
        $manager = $services->get('ControllerPluginManager');
        $manager->setService('params', $mockParams);

        $this->assertSame('unit-test-bookmark', $plugin->getBookmarkNameFromRoute());

        $document = $plugin->getDocument();
        $this->assertInstanceOf('Prismic\Document', $document);

        return $document;
    }

    /**
     * @depends testMockParams
     */
    public function testGetRouteParamsForDocument(Document $doc)
    {
        $params = $this->plugin->getRouteParamsForDocument($doc);
        $this->assertInternalType('array', $params);

        $this->assertContains($doc->getId(), $params);
        $this->assertContains($doc->getType(), $params);
    }

}
