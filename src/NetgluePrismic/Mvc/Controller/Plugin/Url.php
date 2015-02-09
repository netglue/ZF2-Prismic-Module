<?php
/**
 * A controller plugin for generating URLs to Prismic Documents
 */

namespace NetgluePrismic\Mvc\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use NetgluePrismic\Mvc\LinkResolver;
use NetgluePrismic\Mvc\LinkGenerator;

class Url extends AbstractPlugin
{

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
     * Invoke. Returns sel with no argument or the string url for the given target
     * @param  \Prismic\Document|string $target If non-null, hould be a document or an id
     * @return string|self              Returns self when no argument is provided or the string url of the argument
     */
    public function __invoke($target = null)
    {
        if (!is_null($target)) {
            return $this->url($target);
        }

        return $this;
    }

    /**
     * Given a document or document id, return the url for it as a string
     * @param  mixed  $target
     * @return string
     */
    public function url($target)
    {
        return $this->linkResolver->resolve(
            $this->linkGenerator->generate($target)
        );
    }

    /**
     * Get Link Resolver
     * @return LinkResolver
     */
    public function getLinkResolver()
    {
        return $this->linkResolver;
    }

}
