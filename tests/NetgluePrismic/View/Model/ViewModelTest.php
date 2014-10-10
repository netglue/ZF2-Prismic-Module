<?php

namespace NetgluePrismic\View\Model;

class ViewModelTest extends \Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase
{

    private $resolver;

    public function setUp()
    {
        $this->setApplicationConfig(include __DIR__ . '/../../../TestConfig.php.dist');
        parent::setUp();
        $services = $this->getApplicationServiceLocator();
        $this->resolver = $services->get('NetgluePrismic\Mvc\LinkResolver');
    }

    public function getDocument()
    {
        $json = json_decode(file_get_contents(__DIR__ . '/../../../fixtures/document.json'));

        return \Prismic\Document::parse($json);
    }

    public function testSetGetLinkResolver()
    {
        $view = new DocumentViewModel;
        $this->assertNull($view->getLinkResolver());
        $view->setLinkResolver($this->resolver);
        $this->assertSame($this->resolver, $view->getLinkResolver());
    }

    public function testSetGetDocument()
    {
        $view = new DocumentViewModel;
        $view->setLinkResolver($this->resolver);

        $document = $this->getDocument();

        $this->assertFalse(isset($view->meta_title));
        $view->setDocument($document);
        $this->assertSame($document, $view->getDocument());

        $this->assertTrue(isset($view->meta_title));
        $this->assertTrue(isset($view->meta_description));
        $this->assertTrue(isset($view->og_image));
    }

    public function testSetDocumentClearsVars()
    {
        $view = new DocumentViewModel;
        $view->setLinkResolver($this->resolver);

        $document = $this->getDocument();

        $view->should_be_cleared = 'How Do?';
        $this->assertTrue(isset($view->should_be_cleared));

        $view->setDocument($document);

        $this->assertFalse(isset($view->should_be_cleared));
    }

}
