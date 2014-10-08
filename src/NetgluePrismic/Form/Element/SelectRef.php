<?php

namespace NetgluePrismic\Form\Element;

use Zend\Form\Element\Select;
use Prismic\Ref;
use DateTime;

class SelectRef extends Select
{

    public function setRefs(array $refs)
    {
        $future = $options = array();

        foreach($refs as $ref) {
            $value = $ref->getRef();
            if($ref->isMasterRef()) {
                $options[$value] = $this->createLabel($ref);
            } else {
                $future[$value] = $this->createLabel($ref);
            }
        }
        if(count($future)) {
            $options[] = array(
                'label' => 'Future Releases',
                'options' => $future
            );
        }
        $this->setValueOptions($options);
    }

    /**
     * Return a label for the given ref
     * @param  Ref $ref
     * @return string
     */
    public function createLabel(Ref $ref)
    {
        $timestamp = $ref->getScheduledAt();
        if(null !== $timestamp) {

            $date = new DateTime;
            $date->setTimestamp($timestamp);
            $timestamp = sprintf("(Scheduled for release on %s)",
                $date->format('jS F Y'));
        }
        return trim(sprintf("%s %s",
            $ref->getLabel(),
            $timestamp));
    }

}
