<?php

namespace NetgluePrismic\View\Helper;

use NetgluePrismic\Context;
use NetgluePrismic\ContextAwareInterface;

use Zend\View\Helper\AbstractHelper;

abstract class AbstractPrismicHelper extends AbstractHelper implements ContextAwareInterface
{
    /**
     * Context Instance
     * @var Context
     */
    protected $prismicContext;

    /**
     * Set the Prismic Ref for this instance
     * @param Context $context
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
