<?php

namespace NetgluePrismic\Mvc;

use Prismic\Fragment\Link\DocumentLink;

class LinkGeneratorTest extends \Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase
{

    private $document;

    public function setUp()
    {
        $this->setApplicationConfig(include __DIR__ . '/../../TestConfig.php.dist');
        parent::setUp();
        $json = json_decode(file_get_contents(__DIR__ . '/../../fixtures/document.json'));
        $this->document = \Prismic\Document::parse($json);
    }

    public function testNewInstance()
    {
        $services = $this->getApplicationServiceLocator();
        $context = $services->get('NetgluePrismic\Context');
        $generator = new LinkGenerator($context);
        $this->assertSame($context, $generator->getContext());
    }

    public function testFactoryInstance()
    {
        $services = $this->getApplicationServiceLocator();
        $generator = $services->get('NetgluePrismic\Mvc\LinkGenerator');
        $this->assertInstanceOf('NetgluePrismic\Mvc\LinkGenerator', $generator);

        return $generator;
    }

    /**
     * @depends testFactoryInstance
     */
    public function testGetDocumentLink(LinkGenerator $gen)
    {
        $link = $gen->getDocumentLink($this->document);
        $this->assertInstanceOf('Prismic\Fragment\Link\DocumentLink', $link);
        $this->assertSame($this->document->getId(), $link->getId());
    }

    /**
     * @depends testFactoryInstance
     */
    public function testGenerateWithId(LinkGenerator $gen)
    {
        $id = 'VDRgLysAACoAfWTE';
        $link = $gen->generate($id);
        $this->assertInstanceOf('Prismic\Fragment\Link\DocumentLink', $link);
        $this->assertSame($id, $link->getId());

        return $link;
    }

    /**
     * @depends testFactoryInstance
     */
    public function testLinkIsReturnedUnmodified(LinkGenerator $gen)
    {
        $id = 'VDRgLysAACoAfWTE';
        $link = $gen->generate($id);

        $this->assertSame($link, $gen->generate($link));
    }

    /**
     * @expectedException NetgluePrismic\Exception\InvalidArgumentException
     * @depends testFactoryInstance
     */
    public function testExceptionIsThrownForInvalidArg(LinkGenerator $gen)
    {
        $gen->generate(array());
    }

    /**
     * @expectedException NetgluePrismic\Exception\InvalidArgumentException
     * @depends testFactoryInstance
     */
    public function testExceptionIsThrownForInvalidId(LinkGenerator $gen)
    {
        $gen->generate('unknown');
    }

    /**
     * @depends testFactoryInstance
     */
    public function testGenerateAcceptsDocument(LinkGenerator $gen)
    {
        $link = $gen->generate($this->document);
        $this->assertInstanceOf('Prismic\Fragment\Link\DocumentLink', $link);
        $this->assertSame($this->document->getId(), $link->getId());
    }

}
