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
     * @param  Context $context
     * @return void
     */
    public function setContext(Context $context)
    {
        $this->prismicContext = $context;
        if (!isset($this->ref)) {
            $this->ref = (string) $context->getRef();
        } else {
            $refObject = $context->getRefWithString($this->ref);
            if (is_object($refObject)) {
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

        if (!in_array($this->ref, $allRefs, true)) {
            $this->ref = (string) $context->getMasterRef();
            $context->setRef($context->getMasterRef());
        }
    }

    /**
     * Set Access Token for previewing releases
     * @param string $token
     * @return void
     */
    public function setAccessToken($token)
    {
        $this->access_token = $token;
    }

    /**
     * Return the access token for previewing releases
     * @return string|null
     */
    public function getAccessToken()
    {
        return $this->access_token;
    }

    /**
     * Whether an access token has been set
     * @return bool
     */
    public function hasAccessToken()
    {
        return isset($this->access_token) && !empty($this->access_token);
    }


}
