<?php
namespace NetgluePrismic\Mvc\Controller\Plugin;

use Prismic\Document;

class UrlTest extends \Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase
{

    protected $plugin;

    public function setUp()
    {
        $this->setApplicationConfig(include __DIR__ . '/../../../../TestConfig.php.dist');
        parent::setUp();
    }

    public function testGetInstance()
    {
        $services = $this->getApplicationServiceLocator();
        $manager = $services->get('ControllerPluginManager');
        $plugin = $manager->get('NetgluePrismic\Mvc\Controller\Plugin\Url');
        $this->assertInstanceOf('NetgluePrismic\Mvc\Controller\Plugin\Url', $plugin);

        return $plugin;
    }

    /**
     * @depends testGetInstance
     */
    public function testGetLinkResolver(Url $plugin)
    {
        $lr = $plugin()->getLinkResolver();
        $this->assertInstanceOf('NetgluePrismic\Mvc\LinkResolver', $lr);
    }

    /**
     * @depends testGetInstance
     */
    public function testInvoke(Url $plugin)
    {
        $this->assertSame('/test-bookmark', $plugin('VDRgLysAACoAfWTE'));
    }
}
