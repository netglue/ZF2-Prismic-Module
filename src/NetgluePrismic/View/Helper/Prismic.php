<?php

/**
 * This view helper makes it easy to deal with the prismic document that
 * matches the current request, though you can change the document with
 * setDocument() at any time.
 *
 * Nothing special here but the general mode of operation is that any call to
 * find a fragment, or it's HTML or Text value will return null if the named
 * fragment does not exist.
 *
 * It might be desirable to have a configuration option for 'throwExceptions'
 * so that this behaviour can be changed, i.e. throw exceptions for non-
 * existent fragments rather than returning null.
 */

namespace NetgluePrismic\View\Helper;

use Zend\View\Helper\AbstractHelper;

use Prismic\Document;
use Prismic\LinkResolver;

class Prismic extends AbstractHelper
{

    /**
     * Document
     * @var Document
     */
    protected $document;

    /**
     * @var LinkResolver
     */
    protected $linkResolver;

    /**
     * Invoke
     * @return self
     */
    public function __invoke()
    {
        return $this;
    }

    /**
     * Set the document we'll be retrieving content from
     * @param  Document $document
     * @return self
     */
    public function setDocument(Document $document)
    {
        $this->document = $document;

        return $this;
    }

    /**
     * Return the current document we're dealing with
     * @return Document|null
     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * Set the Link Resolver
     * @param  LinkResolver $linkResolver
     * @return self
     */
    public function setLinkResolver(LinkResolver $linkResolver)
    {
        $this->linkResolver = $linkResolver;

        return $this;
    }

    /**
     * Get Link Resolver
     * @return LinkResolver
     */
    public function getLinkResolver()
    {
        return $this->linkResolver;
    }

    /**
     * Return the fragment that corresponds to the given field name
     *
     * A fully qualified field name is required such as 'my-type.my-field'
     * @return \Prismic\Fragment\FragmentInterface|null
     */
    public function get($field)
    {
        $document = $this->getDocument();
        if ($document && $document->has($field)) {
            return $document->get($field);
        }

        return null;
    }

    /**
     * Return the HTML value of the given fragment name
     * A fully qualified field name is required such as 'my-type.my-field'
     * @return string|null
     */
    public function getHtml($field)
    {
        $fragment = $this->get($field);
        if ($fragment) {
            return $fragment->asHtml($this->getLinkResolver());
        }

        return null;
    }

    /**
     * Return the text value of the given fragment name
     * A fully qualified field name is required such as 'my-type.my-field'
     * @return string|null
     */
    public function getText($field)
    {
        $fragment = $this->get($field);
        if ($fragment) {
            return $fragment->asText();
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
