<?php

/**
 * A view helper that renders a link/button to edit the given document on the prismic web app
 */

namespace NetgluePrismic\View\Helper;

use Zend\View\Helper\AbstractHelper;

use Prismic\Document;
use Prismic\LinkResolver;

class Prismic extends AbstractHelper
{

    protected $document;

    protected $linkResolver;

    /**
     * Invoke
     * @return self
     */
    public function __invoke() {
        return $this;
    }

    public function setDocument(Document $document)
    {
        $this->document = $document;

        return $this;
    }

    public function getDocument()
    {
        return $this->document;
    }

    public function setLinkResolver(LinkResolver $linkResolver)
    {
        $this->linkResolver = $linkResolver;

        return $this;
    }

    public function getLinkResolver()
    {
        return $this->linkResolver;
    }

    public function getHtml($field)
    {
        $document = $this->getDocument();
        if($document && $document->has($field)) {
            return $document->get($field)->asHtml($this->getLinkResolver());
        }
        return null;
    }

    public function getText($field)
    {
        $document = $this->getDocument();
        if($document && $document->has($field)) {
            return $document->get($field)->asText();
        }
        return null;
    }

    /**
     * Render
     * @return string
     */
    public function __toString()
    {
        return '';
    }

}
