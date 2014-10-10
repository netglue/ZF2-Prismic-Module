<?php

namespace NetgluePrismic\View\Helper;

use NetgluePrismic\bootstrap;

class EditAtPrismicTest extends \PHPUnit_Framework_TestCase
{

    protected $document;

    public function setUp()
    {
        $json = json_decode(file_get_contents(__DIR__ . '/../../../fixtures/document.json'));
        $this->document = \Prismic\Document::parse($json);
    }

    public function testSetGetDocument()
    {
        $services = bootstrap::getServiceManager();
        $manager = $services->get('ViewHelperManager');

        $helper = $manager->get('NetgluePrismic\View\Helper\EditAtPrismic');

        $this->assertNull($helper->getDocument());
        $helper->setDocument($this->document);
        $this->assertSame($this->document, $helper->getDocument());

        return $helper;
    }

    /**
     * @depends testSetGetDocument
     */
    public function testRender(EditAtPrismic $helper)
    {
        $helper->setTemplate('%1$s|%2$s');
        $helper->setLinkText('Foo');
        $string = (string) $helper($this->document);
        list($url, $text) = explode('|', $string);
        $this->assertSame('Foo', $text);
        $this->assertEquals('http://lesbonneschoses.prismic.io/documents~id=UjHYesuvzT0A_yi6', $url);
    }

    public function testRenderReturnsEmptyStringWithNoDcument()
    {
        $helper = new EditAtPrismic;
        $this->assertSame('', (string) $helper());
    }
}
