<?php

/**
 * A view helper that renders a link/button to edit the given document on the prismic web app
 */

namespace NetgluePrismic\View\Helper;

use Zend\View\Helper\AbstractHelper;

use Prismic\Document;

class Prismic extends AbstractPrismicHelper
{

    /**
     * Invoke - within views simply : echo $this->editAtPrismic($document);
     * @param Document $document
     * @return self
     */
    public function __invoke() {
        return $this;
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
