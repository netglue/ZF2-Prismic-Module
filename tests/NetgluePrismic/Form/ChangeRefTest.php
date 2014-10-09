<?php

namespace NetgluePrismic\Form;

use NetgluePrismic\bootstrap;

class ChangeRefTest extends \PHPUnit_Framework_TestCase
{

    private $form;

    public function setUp()
    {
        $services = bootstrap::getServiceManager();
        $manager = $services->get('FormElementManager');
        $this->form = $manager->get('NetgluePrismic\Form\ChangeRef');
    }

    public function testBasic()
    {
        $select = $this->form->get('ref');
        $this->assertInstanceOf('Zend\Form\Element\Select', $select);
        $this->assertGreaterThanOrEqual(1, $select->getValueOptions());

        $this->assertInstanceOf('Zend\Form\Element', $this->form->get('change-ref'));
    }

}
