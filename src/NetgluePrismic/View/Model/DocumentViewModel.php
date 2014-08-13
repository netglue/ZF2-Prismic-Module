<?php

namespace NetgluePrismic\View\Model;

use Zend\View\Model\ViewModel;
use Prismic\Document;
use Prismic\LinkResolver;

class DocumentViewModel extends ViewModel
{

    /**
     * Prismic Document
     * @var Document|NULL
     */
    protected $document;

    /**
     * Some Link Resolver implementation
     * @var LinkResolver|NULL
     */
    protected $linkResolver;

    /**
     * Set the document the View is intended to present
     *
     * This method first clears all variables before setting each named fragment of the document to
     * a view variable as HTML using the link resolver in $this->getLinkResolver()
     *
     * @param Document $document
     * @return self
     */
    public function setDocument(Document $document)
    {
        $this->clearVariables();
        $this->document = $document;
        $this->setVariable('document', $document);

        $type = $document->getType();

        foreach($document->getFragments() as $name => $fragment) {
            $var = str_replace($type.'.', '', $name);
            $html = $fragment->asHtml($this->getLinkResolver());
            $this->setVariable($var, $html);
        }

        return $this;
    }

    /**
     * Return document set if any
     * @return Document|NULL
     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * Return injected Link Resolver instance
     * @return LinkResolver|NULL
     */
    public function getLinkResolver()
    {
        return $this->linkResolver;
    }

    /**
     * Set the link resolver to use for linking between documents
     * @param LinkResolver $linkResolver
     * @return self
     */
    public function setLinkResolver(LinkResolver $linkResolver)
    {
        $this->linkResolver = $linkResolver;

        return $this;
    }



}
