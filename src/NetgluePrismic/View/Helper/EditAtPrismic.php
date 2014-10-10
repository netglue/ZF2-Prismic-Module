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

    /**
     * Template for the link in sprintf format
     *
     * First position is the link url, second position is the link text
     * @var string
     */
    protected $template = '<a class="prismic-edit" title="Edit this document" href="%1$s">%2$s</a>';

    /**
     * Link text or markup to render with the anchor
     * @var string
     */
    protected $linkText = 'Edit Document';

    /**
     * Set the link template markup
     * @param  string $template
     * @return self
     */
    public function setTemplate($template)
    {
        $this->template = (string) $template;

        return $this;
    }

    /**
     * Set the link text
     * @param  string $text
     * @return self
     */
    public function setLinkText($text)
    {
        $this->linkText = (string) $text;

        return $this;
    }

    /**
     * Set the document being linked to
     * @param  Document $document
     * @return self
     */
    public function setDocument(Document $document)
    {
        $this->document = $document;

        return $this;
    }

    /**
     * Return linked document
     * @return Document|NULL
     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * Render with the current document
     * @return string
     */
    public function render()
    {
        if ($this->document) {
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

            return sprintf($this->template, $url, $this->linkText);
        }

        return '';
    }

    /**
     * Invoke - within views simply : echo $this->editAtPrismic($document);
     * @param  Document $document
     * @return self
     */
    public function __invoke(Document $document = NULL)
    {
        if ($document) {
            $this->setDocument($document);
        }

        return $this;
    }

    /**
     * Render
     * @return string
     */
    public function __toString()
    {
        return $this->render();
    }

}
