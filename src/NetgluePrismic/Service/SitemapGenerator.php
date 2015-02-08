<?php

namespace NetgluePrismic\Service;

use NetgluePrismic\ContextAwareInterface;
use NetgluePrismic\ContextAwareTrait;
use NetgluePrismic\ApiAwareInterface;
use NetgluePrismic\ApiAwareTrait;
use NetgluePrismic\Mvc\LinkResolver;
use NetgluePrismic\Mvc\LinkGenerator;

use Prismic\Document;
use Prismic\SearchForm;
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
     * @var string A name for this collection of URLs/Pages
     */
    protected $name;

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
        'priority'   => 'priority',
        'changefreq' => 'change_freq',
        'lastmod'    => null,
    );

    /**
     * @param LinkResolver  $resolver
     * @return void
     */
    public function setLinkResolver(LinkResolver $resolver)
    {
        $this->linkResolver = $resolver;
    }

    /**
     * @param LinkGenerator $generator
     * @return void
     */
    public function setLinkGenerator(LinkGenerator $generator)
    {
        $this->linkGenerator = $generator;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Sets the document types to search for in the Prismic Api
     * @param array $types
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
     * @param int $page
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

    /**
     * Return *all* documents of the configured types
     * @return array An array of Document instances
     */
    public function retrieveDocuments()
    {
        $response = $this->retrieveDocumentsByPage(1);
        $documents = $response->getResults();
        while($response->getPage() < $response->getTotalPages()) {
            $page = $response->getPage() + 1;
            $response = $this->retrieveDocumentsByPage($page);
            $documents = array_merge($documents, $response->getResults());
        }
        return $documents;
    }

    /**
     * Given a Prismic document, return an array suitable for generating a sitemap entry or null
     * @param Document $document
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

        foreach($this->propertyMap as $property => $fragment) {
            if (empty($fragment)) {
                continue;
            }
            $fragment = sprintf('%s.%s', $type, $fragment);
            $frag = $document->get($fragment);
            if($frag) {
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
        foreach($docs as $doc) {
            $page = $this->documentToArray($doc);
            if(null !== $page) {
                $pages[] = $page;
            }
        }
        $this->getContainer()->addPages($pages);
    }
}
