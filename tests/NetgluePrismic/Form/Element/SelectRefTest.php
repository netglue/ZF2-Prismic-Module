<?php

namespace NetgluePrismic\Form\Element;

use NetgluePrismic\bootstrap;

class SelectRefTest extends \PHPUnit_Framework_TestCase
{

    private $context;

    public function setUp()
    {
        $services = bootstrap::getServiceManager();
        $this->context = $services->get('Prismic\Context');
        $this->context->getPrismicApi()->getCache()->clear();
    }

    public function testBasic()
    {
        $refs = $this->context->getPrismicApi()->refs();
        $this->assertGreaterThanOrEqual(1, count($refs));
        $select = new SelectRef('test');
        $select->setRefs($refs);
        $this->assertGreaterThanOrEqual(1, count($select->getValueOptions()));

    }

}
