<?php

namespace NetgluePrismic;

use Prismic\Api;
use Prismic\Ref;

class Context implements ApiAwareInterface
{
    /**
     * Prismic Api Instance
     * @var Api|NULL
     */
    protected $prismicApi;

    /**
     * Prismic Ref Instance
     * @var Ref|NULL
     */
    protected $ref;

    /**
     * Set the Prismic Api Instance
     * @param Api $api
     * @return void
     */
    public function setPrismicApi(Api $api)
    {
        $this->prismicApi = $api;
    }

    /**
     * Return Prismic Api instance
     * @return Api|NULL
     */
    public function getPrismicApi()
    {
        return $this->prismicApi;
    }


    /**
     * Set the Prismic Ref
     * @param Ref $ref
     * @return void
     */
    public function setRef(Ref $ref)
    {
        $this->ref = $ref;
    }

    /**
     * Return Current context/ref
     * @return Ref
     */
    public function getRef()
    {
        if(!$this->ref) {
            $this->setRef($this->getMasterRef());
        }

        return $this->ref;
    }

    /**
     * Return the master Ref
     * @return Ref
     */
    public function getMasterRef()
    {
        return $this->getPrismicApi()->master();
    }

    /**
     * Return the string ref for the selected context
     * @return string
     */
    public function __toString()
    {
        return (string) $this->getRef();
    }
}
