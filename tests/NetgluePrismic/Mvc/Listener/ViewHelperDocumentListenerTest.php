<?php
namespace NetgluePrismic\Mvc\Listener;


class ViewHelperDocumentListenerTest extends \Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase
{

    private $manager;

    private $context;

    public function setUp()
    {
        $this->setApplicationConfig(include __DIR__ . '/../../../TestConfig.php.dist');
        parent::setUp();

        $services = $this->getApplicationServiceLocator();
        $this->manager = $services->get('ViewHelperManager');
        $this->context = $services->get('Prismic\Context');
    }

    public function testFactory()
    {
        $services = $this->getApplicationServiceLocator();
        $listener = $services->get('NetgluePrismic\Mvc\Listener\ViewHelperDocumentListener');
        $this->assertInstanceOf('NetgluePrismic\Mvc\Listener\ViewHelperDocumentListener', $listener);
    }

    public function testCanCreateInstance()
    {
        $listener = new ViewHelperDocumentListener($this->manager);
        $this->assertInstanceOf('NetgluePrismic\Mvc\Listener\ViewHelperDocumentListener', $listener);

        return $listener;
    }


    public function testDocumentIsSetInHelper()
    {
        $services = $this->getApplicationServiceLocator();
        $listener = $services->get('NetgluePrismic\Mvc\Listener\ViewHelperDocumentListener');

        $helper = $this->manager->get('NetgluePrismic\View\Helper\Prismic');

        $this->assertInstanceOf('NetgluePrismic\View\Helper\Prismic', $helper);

        $document = $this->context->getDocumentByBookmark('unit-test-bookmark');
        $this->assertInstanceOf('Prismic\Document', $document);

        $this->assertNull($helper->getDocument());

        $event = new \Zend\EventManager\Event('Some-Event', NULL, array('document' => $document));
        $listener->onSetDocument($event);

        $this->assertInstanceOf('Prismic\Document', $helper->getDocument());
        $this->assertSame($document, $helper->getDocument());
    }


}
