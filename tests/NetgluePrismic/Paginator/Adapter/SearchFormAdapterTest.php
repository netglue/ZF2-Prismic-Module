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
        $form = $this->api->forms()->pagination;
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


    }

}
