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

    protected $linkResolver;

    public function setDocument(Document $document)
    {
        $this->clearVariables();
        $this->document = $document;

        $type = $document->getType();

        foreach($document->getFragments() as $name => $fragment) {
            $var = str_replace($type.'.', '', $name);
            $html = $fragment->asHtml($this->getLinkResolver());
            $this->setVariable($var, $html);
        }

        return $this;
    }

    public function getDocument()
    {
        return $this->document;
    }

    public function getLinkResolver()
    {
        return $this->linkResolver;
    }

    public function setLinkResolver(LinkResolver $linkResolver)
    {
        $this->linkResolver = $linkResolver;

        return $this;
    }



}
