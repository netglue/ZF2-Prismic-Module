<?php

namespace NetgluePrismic\View\Helper;

use NetgluePrismic\bootstrap;

class PrismicTest extends \PHPUnit_Framework_TestCase
{

    protected $document;

    public function setup()
    {
        $services = bootstrap::getServiceManager();
        $context = $services->get('Prismic\Context');
        $this->document = $context->getDocumentById('VDRgLysAACoAfWTE');
    }

    public function testGetLinkResolver()
    {
        $services = bootstrap::getServiceManager();
        $linkResolver = $services->get('NetgluePrismic\Mvc\LinkResolver');
        $this->assertInstanceOf('NetgluePrismic\Mvc\LinkResolver', $linkResolver);
    }

    public function testGetViewHelper()
    {
        $services = bootstrap::getServiceManager();
        $manager = $services->get('ViewHelperManager');
        $helper = $manager->get('NetgluePrismic\View\Helper\Prismic');
        $this->assertInstanceOf('NetgluePrismic\View\Helper\Prismic', $helper);
        $this->assertSame($helper, $manager->get('prismic'));

        $this->assertInstanceOf('NetgluePrismic\Mvc\LinkResolver', $helper->getLinkResolver());
    }



    public function getHelper()
    {
        $services = bootstrap::getServiceManager();
        $manager = $services->get('ViewHelperManager');
        return $manager->get('NetgluePrismic\View\Helper\Prismic');
    }

    public function testGettersAllReturnNullWhenThereIsNoDocument()
    {
        $helper = $this->getHelper();
        $this->assertNull($helper->getDocument());

        $this->assertFalse($helper->has('some-field'));
        $this->assertNull($helper->get('some-field'));
        $this->assertNull($helper->getText('some-field'));
        $this->assertNull($helper->getHtml('some-field'));
    }

    public function testHelperImplementsToString()
    {
        $helper = $this->getHelper();
        $this->assertEquals('', (string) $helper);
    }

    public function testInvokeReturnsSelf()
    {
        $helper = $this->getHelper();
        $this->assertSame($helper, $helper());
    }

    public function testSetGetDocument()
    {
        $helper = $this->getHelper();
        $this->assertNull($helper->getDocument());

        $this->assertInstanceOf('Prismic\Document', $this->document);
        $helper->setDocument($this->document);
        $this->assertSame($this->document, $helper->getDocument());
    }

    /**
     * @depends testSetGetDocument
     */
    public function testExpectedValuesFromGetters()
    {
        $helper = $this->getHelper();

        $value = $helper->get('test.test-text');
        $this->assertInstanceOf('Prismic\Fragment\Text', $value);
        $this->assertEquals('Don\'t change me unless you want unit tests to fail', $value->asText());

        $this->assertEquals('Don\'t change me unless you want unit tests to fail', $helper->getText('test.test-text'));
        $this->assertStringMatchesFormat('%s', $helper->getHtml('test.test-text'));
    }



}
