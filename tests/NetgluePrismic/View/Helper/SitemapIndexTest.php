<?php

namespace NetgluePrismic\View\Helper;

use NetgluePrismic\bootstrap;
use DOMDocument;

class SitemapIndexTest extends \Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase
{

    public function setup()
    {
        $this->setApplicationConfig(include __DIR__ . '/../../../TestConfig.php.dist');
        parent::setUp();
        $services = $this->getApplicationServiceLocator();
        $context = $services->get('Prismic\Context');
        //$this->document = $context->getDocumentById('VDRgLysAACoAfWTE');
    }

    public function testGetViewHelper()
    {
        $services = $this->getApplicationServiceLocator();
        $manager = $services->get('ViewHelperManager');
        $helper = $manager->create('NetgluePrismic\View\Helper\SitemapIndex');
        $this->assertInstanceOf('NetgluePrismic\View\Helper\SitemapIndex', $helper);

        return $helper;
    }

    /**
     * @depends testGetViewHelper
     */
    public function testSetGetFormatOutput(SitemapIndex $helper)
    {
        $this->assertInternalType('bool', $helper->getFormatOutput());
        $helper->setFormatOutput(true);
        $this->assertTrue($helper->getFormatOutput());
    }

    /**
     * @depends testGetViewHelper
     */
    public function testGetServerUrl(SitemapIndex $helper)
    {
        $_SERVER['HTTP_HOST'] = 'example.com';
        $this->assertEquals('http://example.com', $helper->getServerUrl());
    }

    /**
     * @depends testGetViewHelper
     */
    public function testUrlReturnsPrefixedUrl(SitemapIndex $helper)
    {
        $url = $helper->url('/somewhere');
        $this->assertEquals('http://example.com/somewhere', $url);
    }

    /**
     * @depends testGetViewHelper
     */
    public function testUrlReturnsUnPrefixedUrl(SitemapIndex $helper)
    {
        $url = $helper->url('http://foobar.com/somewhere');
        $this->assertEquals('http://foobar.com/somewhere', $url);
    }

    /**
     * @depends testGetViewHelper
     */
    public function testUrlReturnsNullWhenAppropriate(SitemapIndex $helper)
    {
        $url = $helper->url(array());
        $this->assertNull($url);

        $url = $helper->url('#foo');
        $this->assertNull($url);
    }

    /**
     * @depends testGetViewHelper
     */
    public function testGetUrlEscapes(SitemapIndex $helper)
    {
        $url = $helper->url('/somewhere?foo=1&blah=blah');
        $this->assertEquals('http://example.com/somewhere?foo=1&amp;blah=blah', $url);
    }

    /**
     * @depends testGetViewHelper
     */
    public function testInvokeReturnsSelf(SitemapIndex $helper)
    {
        $this->assertSame($helper, $helper());
    }

    /**
     * @depends testGetViewHelper
     */
    public function testInvokeAcceptsUrlList(SitemapIndex $helper)
    {
        $urls = array('/foo', '/bar');
        $this->assertCount(0, $helper->getUrls());
        $this->assertSame($helper, $helper($urls));
        $this->assertCount(2, $helper->getUrls());
    }

    /**
     * @depends testGetViewHelper
     * @expectedException NetgluePrismic\Exception\InvalidArgumentException
     */
    public function testAddUrlThrowsExceptionForNonStringUrl(SitemapIndex $helper)
    {
        $helper->addUrl(true);
    }

    /**
     * @depends testGetViewHelper
     * @expectedException NetgluePrismic\Exception\InvalidArgumentException
     */
    public function testAddUrlThrowsExceptionForNonStringLastmod(SitemapIndex $helper)
    {
        $helper->addUrl('/foo', false);
    }

    /**
     * @depends testGetViewHelper
     */
    public function testSetUrlsWithArrays(SitemapIndex $helper)
    {
        $date = date('c');
        $urls = array(
            array('/foo', $date),
            array('/bar', $date),
        );

        $expect = array(
            array('url' => '/foo', 'lastmod' => $date),
            array('url' => '/bar', 'lastmod' => $date),
        );

        $helper->setUrls($urls);
        $this->assertSame($expect, $helper->getUrls());
    }

    /**
     * @depends testGetViewHelper
     */
    public function testExpectedOutput(SitemapIndex $helper)
    {
        $date = date('c');
        $urls = array(
            array('/foo', $date),
            array('/bar', $date),
        );
        $helper->setUrls($urls);
        $helper->setFormatOutput(false);
        $helper->addUrl('#foo'); // This url should not appear in output
        $expect = '<?xml version="1.0" encoding="UTF-8"?>
<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"><sitemap><loc>http://example.com/foo</loc><lastmod>'.$date.'</lastmod></sitemap><sitemap><loc>http://example.com/bar</loc><lastmod>'.$date.'</lastmod></sitemap></sitemapindex>';
        $this->assertSame($expect, $helper->render());
    }

}
