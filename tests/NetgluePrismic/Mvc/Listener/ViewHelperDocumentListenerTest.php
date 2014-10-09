<?php
namespace NetgluePrismic\Mvc\Listener;

use NetgluePrismic\bootstrap;

class ViewHelperDocumentListenerTest extends \PHPUnit_Framework_TestCase
{

    private $manager;

    private $context;

    public function setUp()
    {
        $services = bootstrap::getServiceManager();
        $this->manager = $services->get('ViewHelperManager');
        $this->context = $services->get('Prismic\Context');
    }

    public function testCanCreateInstance()
    {
        $listener = new ViewHelperDocumentListener($this->manager);
        $this->assertInstanceOf('NetgluePrismic\Mvc\Listener\ViewHelperDocumentListener', $listener);

        return $listener;
    }

    /**
     * @depends testCanCreateInstance
     */
    public function testDocumentIsSetInHelper(ViewHelperDocumentListener $listener)
    {
        $helper = $this->manager->create('NetgluePrismic\View\Helper\Prismic');
        $this->manager->setAllowOverride(true);
        $this->manager->setService('Prismic', $helper);


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
