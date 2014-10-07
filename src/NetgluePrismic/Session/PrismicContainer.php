<?php

namespace NetgluePrismic\Session;

use Zend\Session\Container;
use NetgluePrismic\ContextAwareInterface;
use NetgluePrismic\ContextAwareTrait;
use NetgluePrismic\Context;

class PrismicContainer extends Container implements ContextAwareInterface
{
    use ContextAwareTrait;


    /**
     * Set the Prismic Ref for this instance
     * @param Context $context
     * @return void
     */
    public function setContext(Context $context)
    {
        $this->prismicContext = $context;
        if(!isset($this->ref)) {
            $this->ref = (string) $context->getRef();
        } else {
            $refObject = $context->getRefWithString($this->ref);
            if(is_object($refObject)) {
                $context->setRef($refObject);
            }
        }

        /**
         * Make Sure the selected ref is valid
         */
        $allRefs = array_map(
            function ($value) {
                return $value->getRef();
            },
            $context->getPrismicApi()->refs()
        );

        if(!in_array($this->ref, $allRefs, true)) {
            $this->ref = (string) $context->getMasterRef();
            $context->setRef($context->getMasterRef());
        }
    }

}
