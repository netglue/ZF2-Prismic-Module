<?php

/**
 * An object that tries to generate document links when given a string or a document
 */

namespace NetgluePrismic\Mvc;

use Prismic\Fragment\Link\LinkInterface;
use Prismic\Fragment\Link\DocumentLink;
use Prismic\Document;
use NetgluePrismic\Exception;
use NetgluePrismic\ContextAwareInterface;
use NetgluePrismic\ContextAwareTrait;
use NetgluePrismic\Context;


class LinkGenerator implements ContextAwareInterface
{

    use ContextAwareTrait;

    /**
     * Depends on a context instance
     * @param Context $context
     */
    public function __construct(Context $context)
    {
        $this->setContext($context);
    }

    /**
     * Given a document instance or document identifier, return a link instance
     * @return DocumentLink
     * @param string|Document|LinkInterface $target
     */
    public function generate($target)
    {
        $link = null;
        $arg = $target;

        if(is_string($target)) {
            // Assume a document id
            $target = $this->getContext()->getDocumentById($target);
            // $target may be null || Document
        }

        if($target instanceof LinkInterface) {
            $link = $target;
        }

        if($target instanceof Document) {
            $link = $this->getDocumentLink($target);
        }

        if(! $link instanceof LinkInterface) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Cannot resolve %s to a document link',
                is_scalar($arg) ? $arg : gettype($arg)));
        }

        return $link;
    }

    /**
     * Return a new link instance to the given Document
     * @param Document $doc
     * @return DocumentLink
     */
    public function getDocumentLink(Document $doc)
    {
        return $doc->asDocumentLink();
    }


}
