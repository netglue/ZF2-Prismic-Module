<?php
namespace NetgluePrismic\Form;
use Zend\Form\Form;
use Zend\InputFilter\InputFilterProviderInterface;

class ChangeRef extends Form implements
    InputFilterProviderInterface
{

    /**
     * Initialise Form
     */
    public function init()
    {
        $this->add(array(
            'name' => 'ref',
            'type' => 'NetgluePrismic\Form\Element\SelectRef',
            'options' => array(
                'label' => 'Choose a release',
            ),
            'attributes' => array(
                'required' => true,
            ),
        ));

        $this->add(array(
            'name' => 'change-ref',
            'type' => 'Zend\Form\Element\Submit',
            'options' => array(),
            'attributes' => array(
                'value' => 'Change',
            ),
        ));

    }

    /**
     * Input Filter Spec
     * @return array
     */
    public function getInputFilterSpecification()
    {
        return array(

        );
    }

}
