<?php

use Zend\Form\Form;
use Zend\InputFilter\InputFilterProviderInterface;

namespace NetgluePrismic\Form\ChangeRef extends Form implements
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
				'label' => 'Choose a Rejection Message or leave blank to have no message sent',
				'empty_option' => 'Don\'t send a message',
			),
			'attributes' => array(
				'required' => false,
				'id' => 'rejectionTemplate',
			),
        ));
    }

    /**
	 * Input Filter Spec
	 * @return array
	 */
	public function getInputFilterSpecification()
	{

	}

}
