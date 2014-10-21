<?php

namespace NetgluePrismic\View\Helper;

use NetgluePrismic\bootstrap;

class UrlTest extends \Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase
{

    protected $document;

    public function setup()
    {
        $this->setApplicationConfig(include __DIR__ . '/../../../TestConfig.php.dist');
        parent::setUp();
        $services = $this->getApplicationServiceLocator();
        $context = $services->get('Prismic\Context');
        $this->document = $context->getDocumentById('VDRgLysAACoAfWTE');
    }

    public function testGetViewHelper()
    {
        $services = $this->getApplicationServiceLocator();
        $manager = $services->get('ViewHelperManager');
        $helper = $manager->create('NetgluePrismic\View\Helper\Url');
        $this->assertInstanceOf('NetgluePrismic\View\Helper\Url', $helper);
        $this->assertInstanceOf('NetgluePrismic\Mvc\LinkResolver', $helper->getLinkResolver());
    }

    public function getHelper()
    {
        $services = $this->getApplicationServiceLocator();
        $manager = $services->get('ViewHelperManager');

        return $manager->get('NetgluePrismic\View\Helper\Url');
    }

    public function testHelperImplementsToString()
    {
        $helper = $this->getHelper();
        $this->assertEquals('', (string) $helper);
    }

    public function testInvokeSetsTarget()
    {
        $link = new \Prismic\Fragment\Link\WebLink('http://www.google.com');
        $helper = $this->getHelper();
        $this->assertSame('http://www.google.com', (string) $helper($link));
    }

    public function testDocumentIdAsTargetIsAcceptable()
    {
        $helper = $this->getHelper();
        $this->assertNotSame('', (string) $helper($this->document->getId()));
    }

    /**
     * @expectedException NetgluePrismic\Exception\InvalidArgumentException
     */
    public function testSetTargetThrowsExceptionForInvalidArg()
    {
        $helper = $this->getHelper();
        $helper->setTarget(1);
    }



}
