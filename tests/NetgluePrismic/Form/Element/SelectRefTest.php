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
        $select = new SelectRef('test');
        $select->setRefs($this->context->getPrismicApi()->refs());

        $services = bootstrap::getServiceManager();
        $manager = $services->get('ViewHelperManager');
        $plugin = $manager->get('formSelect');
        echo $plugin($select);
    }

}
