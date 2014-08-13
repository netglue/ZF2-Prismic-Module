<?php

/**
 * A view helper that renders a link/button to edit the given document on the prismic web app
 */

namespace NetgluePrismic\View\Helper;

use Zend\View\Helper\AbstractHelper;

use Prismic\Document;

class EditAtPrismic extends AbstractHelper
{

    /**
     * @var Document
     */
    protected $document;

    protected $template = '<a class="prismic-edit" title="Edit this document" href="%1$s">Edit %2$s</a>';

    public function setTemplate($template)
    {
        $this->template = (string) $template;

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

    public function render()
    {
        if($this->document) {
            // This gets us the document in the api browser:
            $url = $this->document->getHref();

            /**
             * This is wrong. The URL is:
             * https://repo.prismic.io/documents~id=:id
             */

            $url = parse_url($url);
            $url = sprintf('%s://%s/documents~id=%s',
                $url['scheme'],
                $url['host'],
                $this->document->getId());
            return sprintf($this->template, $url, 'Document');
        }

        return '';
    }

    public function __invoke(Document $document = NULL) {
        if($document) {
            $this->setDocument($document);
        }

        return $this;
    }

    public function __toString()
    {
        return $this->render();
    }

}
