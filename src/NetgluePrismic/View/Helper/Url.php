<?php

namespace NetgluePrismic\View\Helper;

use Zend\View\Helper\AbstractHelper;

use Prismic\Fragment\Link\LinkInterface;
use Prismic\Fragment\Link\DocumentLink;
use Prismic\Document;
use NetgluePrismic\Exception;
use NetgluePrismic\Mvc\LinkResolver;

class Url extends AbstractHelper
{

    /**
     * @var LinkInterface|null The Link Target
     */
    private $target;

    /**
     * @var LinkResolver The link resolver
     */
    private $linkResolver;

    /**
     * Depends on a link resolver
     * @param LinkResolver $resolver
     * @return void
     */
    public function __construct(LinkResolver $resolver)
    {
        $this->linkResolver = $resolver;
    }

    /**
     * Invoke, optionally setting the link target
     */
    public function __invoke($target = null)
    {
        if(!is_null($target)) {
            $this->setTarget($target);
        }

        return $this;
    }

    /**
     * Set the link target
     * @param LinkInterface|Document|string $target An object or the id of a document
     */
    public function setTarget($target)
    {
        $link = null;
        $arg = $target;

        if(is_string($target)) {
            // Assume a document id
            $target = $this->getContext()->getDocumentById($target);
        }

        if($target instanceof LinkInterface) {
            $link = $target;
        }

        if($target instanceof Document) {
            $link = $this->getLink($target);
        }

        if(! $link instanceof LinkInterface) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Cannot resolve %s to a document link',
                is_scalar($arg) ? $arg : gettype($arg)));
        }

        $this->target = $link;

        return $this;
    }

    /**
     * Return the Link Resolver
     * @return LinkResolver
     */
    public function getLinkResolver()
    {
        return $this->linkResolver;
    }

    /**
     * Return api context
     * @return \NetgluePrismic\Context
     */
    protected function getContext()
    {
        return $this->getLinkResolver()->getContext();
    }

    /**
     * Serialize to string
     * @return string
     */
    public function __toString()
    {
        if(!$this->target instanceof LinkInterface) {
            return '';
        }

        return (string) $this->getLinkResolver()->resolve($this->target);
    }

    /**
     * This is likely to go away as we should probably be making links in the document, or via the link resolver perhaps
     * @param Document $doc
     * @return DocumentLink
     */
    private function getLink(Document $doc)
    {
        return new DocumentLink(
            $doc->getId(),
            $doc->getType(),
            $doc->getTags(),
            $doc->getSlug(),
            false
        );
    }

}
