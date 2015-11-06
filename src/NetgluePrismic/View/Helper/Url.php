<?php

namespace NetgluePrismic\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Prismic\Fragment\Link\LinkInterface;
use NetgluePrismic\Mvc\LinkResolver;
use NetgluePrismic\Mvc\LinkGenerator;

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
     * @var LinkGenerator The link generator
     */
    private $linkGenerator;

    /**
     * Depends on a link resolver
     * @param  LinkResolver  $resolver
     * @param  LinkGenerator $generator
     */
    public function __construct(LinkResolver $resolver, LinkGenerator $generator)
    {
        $this->linkResolver = $resolver;
        $this->linkGenerator = $generator;
    }

    /**
     * Invoke, optionally setting the link target
     */
    public function __invoke($target = null)
    {
        if (!is_null($target)) {
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
        $this->target = $this->linkGenerator->generate($target);

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
     * Serialize to string
     * @return string
     */
    public function __toString()
    {
        if (!$this->target instanceof LinkInterface) {
            return '';
        }
        try {
            return (string) $this->getLinkResolver()->resolve($this->target);
        } catch (\Exception $e) {
            return '';
        }
    }

}
