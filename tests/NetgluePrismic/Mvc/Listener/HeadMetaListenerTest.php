<?php

namespace NetgluePrismic\Mvc\Listener;

use NetgluePrismic\bootstrap;

class HeadMetaListenerTest extends \Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase
{

    public function setUp()
    {
        $this->setApplicationConfig(include __DIR__ . '/../../../TestConfig.php.dist');
        parent::setUp();
    }

    public function getDocument()
    {
        $json = json_decode(file_get_contents(__DIR__ . '/../../../fixtures/document.json'));
        return \Prismic\Document::parse($json);
    }

    public function getListener()
    {
        $services = $this->getApplicationServiceLocator();
        return $services->get('NetgluePrismic\Mvc\Listener\HeadMetaListener');
    }

    public function testCreateInstance()
    {

        $listener = $this->getListener();
        $this->assertInstanceOf('NetgluePrismic\Mvc\Listener\HeadMetaListener', $listener);

        return $listener;
    }

    public function testGetHeadMetaHelper()
    {
        $listener = $this->getListener();
        $helper = $listener->headMeta();
        $this->assertInstanceOf('Zend\View\Helper\HeadMeta', $helper);
    }

    public function testOnSetDocumentSetsMeta()
    {
        $document = $this->getDocument();

        $event = new \Zend\EventManager\Event('event-name', NULL, array('document' => $document));

        $listener = $this->getListener();

        $title = $listener->headTitle();
        $meta = $listener->headMeta();

        $this->assertEmpty($title->renderTitle());



        $listener->onSetDocument($event);

        $this->assertEquals('Head Title Text', $title->renderTitle());

        foreach($meta->getContainer()->getArrayCopy() as $item) {
            if($item->type === 'name') {
                $this->assertSame('Meta Description', $item->content);
            }
            if($item->type === 'property') {
                switch($item->property) {
                    case 'og:title': $expect = 'Head Title Text'; break;
                    case 'og:description': $expect = 'Meta Description'; break;
                    case 'og:image': $expect = 'https://prismicio.s3.amazonaws.com/lesbonneschoses/899162db70c73f11b227932b95ce862c63b9df22.jpg'; break;
                }
                if(!isset($expect)) {
                    $this->fail('Unexpected poperty name');
                }
                $this->assertSame($expect, $item->content);
            }
        }
    }

}
