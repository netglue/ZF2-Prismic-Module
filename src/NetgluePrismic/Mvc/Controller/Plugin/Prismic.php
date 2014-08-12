<?php
/**
 * A controller plugin that bundles useful operations for locating prismic documents etc
 */


namespace NetgluePrismic\Mvc\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;

use NetgluePrismic\ApiAwareInterface;
use NetgluePrismic\Context;
use NetgluePrismic\ContextAwareInterface;
use Prismic\Api;
use Prismic\Document;
use NetgluePrismic\Exception;
use NetgluePrismic\Mvc\Router\RouterOptions;

use NetgluePrismic\View\Model\DocumentViewModel;
use Prismic\LinkResolver;

class Prismic extends AbstractPlugin implements ApiAwareInterface, ContextAwareInterface
{

    /**
     * Prismic Api Instance
     * @var Api
     */
    protected $prismicApi;

    /**
     * Context Instance
     * @var Context
     */
    protected $prismicContext;

    /**
     * Router Options
     * @var RouterOptions
     */
    protected $routerOptions;

    /**
     * LinkResolver
     * @var LinkResolver
     */
    protected $linkResolver;

    /**
     * Set the Prismic Ref for this instance
     * @param Context $context
     * @return void
     */
    public function setContext(Context $context)
    {
        $this->prismicContext = $context;
    }

    /**
     * Return Current context
     * @return Context
     */
    public function getContext()
    {
        return $this->prismicContext;
    }

    /**
     * Set the Prismic Api Instance
     * @param Api $api
     * @return void
     */
    public function setPrismicApi(Api $api)
    {
        $this->prismicApi = $api;
    }

    /**
     * Return Prismic Api instance
     * @return Api
     */
    public function getPrismicApi()
    {
        return $this->prismicApi;
    }

    /**
     * Shorthand for $this->prismic()->getPrismicApi()
     * @return Api
     */
    public function api()
    {
        return $this->getPrismicApi();
    }

    public function __invoke()
    {
        return $this;
    }

    /**
     * Return a single document with the given id at the current repo ref
     * @param string $id
     * @return \Prismic\Document|NULL
     */
    public function getDocumentById($id)
    {
        return $this->getContext()->getDocumentById($id);
    }

    /**
     * Return a single document for the given bookmark name
     * @param string $bookmark
     * @return \Prismic\Document
     * @throws
     */
    public function getDocumentByBookmark($bookmark)
    {
        return $this->getContext()->getDocumentByBookmark($bookmark);
    }

    /**
     * Set Router Options so we can figure out parameter names that correspond to the request/route
     * @param RouterOptions $options
     */
    public function setRouterOptions(RouterOptions $options)
    {
        $this->routerOptions = $options;
    }

    /**
     * Whether the current request/route has a bookmark param set that points to a valid bookmark
     * @return bool
     */
    public function isBookmarkRequest()
    {
        $bookmark = $this->getBookmarkNameFromRoute();
        $documentId = $this->api()->bookmark($bookmark);
        return !empty($documentId);
    }

    /**
     * Return the bookmark referenced in the route matched for the current request
     * @return string|NULL
     */
    public function getBookmarkNameFromRoute()
    {
        $params = $this->getController()->plugin('params');
        $search = $this->routerOptions->getBookmarkParam();
        return $params->fromRoute($search);
    }

    /**
     * Return the document mask parameter found in the current matched route
     * @return string|NULL
     */
    public function getMaskFromRoute()
    {
        $params = $this->getController()->plugin('params');
        $search = $this->routerOptions->getMaskParam();
        return $params->fromRoute($search);
    }

    public function createViewModel($document = NULL)
    {
        $model = new DocumentViewModel;
        $model->setLinkResolver($this->getLinkResolver());
        if(!is_null($document)) {
            if(is_string($document)) {
                $document = $this->getDocumentById($id);
            }
            if(!$document instanceof Document) {
                throw new Exception\InvalidArgumentException(sprintf(
                    'Expected a document instance or a valid document id. Received %s',
                    gettype($document) . ( is_scalar($document) ? $document : '')
                ));
            }
            $model->setDocument($document);
        }
        return $model;
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
