<?php

namespace NetgluePrismic\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Prismic\Document;
use NetgluePrismic\ContextAwareInterface;
use NetgluePrismic\ContextAwareTrait;
use NetgluePrismic\Context;

class Finder extends AbstractHelper implements ContextAwareInterface
{

    use ContextAwareTrait;

    /**
     * Requires a context instance
     * @param  Context $context
     * @return void
     */
    public function __construct(Context $context)
    {
        $this->setContext($context);
    }

    /**
     * Invoke
     * @return self
     */
    public function __invoke()
    {
        return $this;
    }

    /**
     * Locate a document by it's id
     * @param  string        $id
     * @return Document|null
     */
    public function getDocumentById($id)
    {
        return $this->getContext()->getDocumentById($id);
    }

}
