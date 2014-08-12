<?php

namespace NetgluePrismic;

interface ContextAwareInterface
{

    /**
     * Set the Prismic Ref for this instance
     * @param Context $context
     * @return void
     */
    public function setContext(Context $context);

    /**
     * Return Current context/ref
     * @return Context
     */
    public function getContext();

}
