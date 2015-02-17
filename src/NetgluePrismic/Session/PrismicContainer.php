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

    /**
     * Set the repository ref to view
     * @param string $ref
     * @return void
     */
    public function setRef($ref)
    {
        $this->ref = (string) $ref;
    }

    /**
     * Return the repository ref set in the session
     * @return string|null
     */
    public function getRef()
    {
        return $this->ref;
    }


}
