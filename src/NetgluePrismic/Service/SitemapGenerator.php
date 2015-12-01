<?php

namespace NetgluePrismic\Service;

use NetgluePrismic\ContextAwareInterface;
use NetgluePrismic\ContextAwareTrait;
use NetgluePrismic\ApiAwareInterface;
use NetgluePrismic\ApiAwareTrait;
use NetgluePrismic\Mvc\LinkResolver;
use NetgluePrismic\Mvc\LinkGenerator;
use NetgluePrismic\Exception;

use Prismic\Document;
use Prismic\Predicates;
use Prismic\Response;

use Zend\Navigation\Navigation as Container;

class SitemapGenerator implements ContextAwareInterface,
                               ApiAwareInterface
{

    use ContextAwareTrait,
        ApiAwareTrait;

    /**
     * @var Container
     */
    protected $container;

    /**
     * @var array An array of Prismic document types - This is how we'll find all the correct documents
     */
    protected $documentTypes = array();

    /**
     * @var int The number of docs to retrieve per query - Set to the current max allowable for now
     */
    protected $pageSize = 100;

    /**
     * @var LinkResolver
     */
    protected $linkResolver;

    /**
     * @var LinkGenerator
     */
    protected $linkGenerator;

    /**
     * @var array An array that maps a prismic fragment name to the property of a Zend\Navigation\Page instance
     */
    protected $propertyMap = array(
        'priority'   => null,
        'changefreq' => null,
        'lastmod'    => null,
    );

    /**
     * An array of strings with keys as fragment names
     * with which to exclude pages that match
     * @var array
     */
    protected $exclude = array(

    );

    /**
     * Set the array that maps sitemap properties to Prismic document fragments
     * @param  array $map
     * @return void
     */
    public function setPropertyMap(array $map)
    {
        foreach ($map as $prop => $frag) {
            if (!is_string($prop)) {
                throw new Exception\InvalidArgumentException('Encountered property map key that was not a string');
            }
            if (!is_string($frag)) {
                throw new Exception\InvalidArgumentException('Encountered fragment identifier that was not a string');
            }
        }
        $this->propertyMap = $map;
    }

    /**
     * Set fragment value exclusions with an array
     * @param array $exclude
     * @return void
     */
    public function setExclusions(array $exclude)
    {
        $this->exclude = array();
        foreach($exclude as $fragment => $value) {
            $this->addExclusion($fragment, $value);
        }
    }

    /**
     * Add an exclusion
     * @param string $fragmentName
     * @param string $value Value to match
     * @return void
     */
    public function addExclusion($fragmentName, $value)
    {
        $this->exclude[$fragmentName] = $value;
    }

    /**
     * @param  LinkResolver $resolver
     * @return void
     */
    public function setLinkResolver(LinkResolver $resolver)
    {
        $this->linkResolver = $resolver;
    }

    /**
     * @param  LinkGenerator $generator
     * @return void
     */
    public function setLinkGenerator(LinkGenerator $generator)
    {
        $this->linkGenerator = $generator;
    }

    /**
     * Sets the document types to search for in the Prismic Api
     * @param  array $types
     * @return void
     */
    public function setDocumentTypes(array $types)
    {
        $this->documentTypes = $types;
    }

    /**
     * Creates and populates a navigation container
     * @return Container
     */
    public function getContainer()
    {
        if (!$this->container) {
            $this->container = new Container;
            $this->populateContainer();
        }

        return $this->container;
    }

    /**
     * Return the prismic response containing the documents in the given page
     * @param  int      $page
     * @return Response
     */
    protected function retrieveDocumentsByPage($page)
    {
        $api        = $this->getPrismicApi();
        $ref        = $this->getContext()->getRef();
        $predicates = array(
            Predicates::any("document.type", $this->documentTypes),
        );
        $form = $api->forms()->everything
                ->ref($ref)
                ->pageSize($this->pageSize)
                ->page($page)
                ->query($predicates);

        return $form->submit();
    }

    private function filterExcludedDocuments(array $docs)
    {
        if(!count($this->exclude)) {
            return $docs;
        }

        foreach($this->exclude as $fragName => $value) {
            $expectType = null;
            $frag = $fragName;
            if(strpos($fragName, '.') !== false) {
                list($expectType, $frag) = explode('.', $fragName);
            }
            foreach($docs as $key => $doc) {
                $type = $doc->getType();
                if($expectType && $expectType !== $type) {
                    continue;
                }
                $compare = null;
                if($doc->get($type . '.' . $frag)) {
                    $compare = $doc->get($type . '.' . $frag)->asText();
                    if($compare === $value) {
                        unset($docs[$key]);
                    }
                }
            }
        }

        return $docs;
    }

    /**
     * Return *all* documents of the configured types
     * @return array An array of Document instances
     */
    protected function retrieveDocuments()
    {
        $response = $this->retrieveDocumentsByPage(1);
        $documents = $response->getResults();
        while ($response->getPage() < $response->getTotalPages()) {
            $page = $response->getPage() + 1;
            $response = $this->retrieveDocumentsByPage($page);
            $documents = array_merge($documents, $response->getResults());
        }

        return $this->filterExcludedDocuments($documents);
    }

    /**
     * Given a Prismic document, return an array suitable for generating a sitemap entry or null
     * @param  Document   $document
     * @return array|null
     */
    protected function documentToArray(Document $document)
    {
        $type = $document->getType();
        $data = array();

        /**
         * If we can't work out the href from the link resolver, we're screwed
         */
        $link = $this->linkGenerator->generate($document);
        $url  = $this->linkResolver->resolve($link);
        if (!$url) {
            return null;
        }

        $data['uri'] = $url;

        foreach ($this->propertyMap as $property => $fragment) {
            if (empty($fragment)) {
                continue;
            }
            $fragment = sprintf('%s.%s', $type, $fragment);
            $frag = $document->get($fragment);
            if ($frag) {
                $data[$property] = $frag->asText();
            }
        }

        return $data;
    }

    /**
     * Iterates over all of the prismic documents found and adds each to a navigation container
     * @return void
     */
    protected function populateContainer()
    {
        $docs = $this->retrieveDocuments();
        $pages = array();
        foreach ($docs as $doc) {
            $page = $this->documentToArray($doc);
            if (null !== $page) {
                $pages[] = $page;
            }
        }
        $this->getContainer()->addPages($pages);
    }
}
