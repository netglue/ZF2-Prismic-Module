<?php

namespace NetgluePrismic\Paginator\Adapter;

use NetgluePrismic\bootstrap;

use Prismic\SearchForm;
use Zend\Paginator\Paginator;
class SearchFormAdapterTest extends \PHPUnit_Framework_TestCase
{

    private $api;

    public function setUp()
    {
        $services = bootstrap::getServiceManager();
        $this->api = $services->get('Prismic\Api');
    }

    public function testApiAccess()
    {
        $form = $this->api->forms()->pagination->ref($this->api->master());
        $this->assertInstanceOf('Prismic\SearchForm', $form);
        $this->assertEquals(6, $form->count());
        return $form;
    }

    /**
     * @depends testApiAccess
     */
    public function testNewInstance(SearchForm $form)
    {
        $adapter = new SearchFormAdapter($form);
        $this->assertSame(6, $adapter->count());

        $pager = new Paginator($adapter);
        $pager->setCurrentPageNumber(1);
        $pager->setItemCountPerPage(2);

        $this->assertSame(6, $pager->getTotalItemCount());

        foreach($pager as $item) {
            $this->assertInstanceOf('Prismic\Document', $item);
            $this->assertSame('pager-test-doc', $item->getType());
        }

        return $pager;
    }

    public function testExpectedBehaviour()
    {
        $form = $this->api->forms()
            ->pagination
            ->ref($this->api->master())
            ->orderings('[my.pager-test-doc.priority]');

        $adapter = new SearchFormAdapter($form);
        $pager = new Paginator($adapter);
        $pager->setCurrentPageNumber(1);
        $pager->setItemCountPerPage(4);

        $items = $pager->getItemsByPage(1);
        $this->assertCount(4, $items);
        $this->assertContainsOnlyInstancesOf('Prismic\Document', $items);
        $this->assertEquals(10, $items[0]->get('pager-test-doc.priority')->asText());
        $this->assertEquals(20, $items[1]->get('pager-test-doc.priority')->asText());
        $this->assertEquals(30, $items[2]->get('pager-test-doc.priority')->asText());
        $this->assertEquals(40, $items[3]->get('pager-test-doc.priority')->asText());

        $items = $pager->getItemsByPage(2);
        $this->assertCount(2, $items);
        $this->assertContainsOnlyInstancesOf('Prismic\Document', $items);
        $this->assertEquals(50, $items[0]->get('pager-test-doc.priority')->asText());
        $this->assertEquals(60, $items[1]->get('pager-test-doc.priority')->asText());
    }

}
