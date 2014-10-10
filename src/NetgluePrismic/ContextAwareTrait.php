<?php

namespace NetgluePrismic;

trait ContextAwareTrait
{

    /**
     * Context Instance
     * @var Context
     */
    protected $prismicContext;

    /**
     * Set the Prismic Ref for this instance
     * @param  Context $context
     * @return void
     */
    public function setContext(Context $context)
    {
        $this->prismicContext = $context;
    }

    /**
     * Return Current context
     * @return Context
     */
    public function getContext()
    {
        return $this->prismicContext;
    }

}
