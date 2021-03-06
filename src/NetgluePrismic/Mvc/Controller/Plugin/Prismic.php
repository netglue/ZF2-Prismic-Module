<?php
/**
 * A controller plugin that bundles useful operations for locating prismic documents etc
 */

namespace NetgluePrismic\Mvc\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerAwareTrait;
use NetgluePrismic\ApiAwareInterface;
use NetgluePrismic\ApiAwareTrait;
use NetgluePrismic\ContextAwareInterface;
use NetgluePrismic\ContextAwareTrait;
use Prismic\Api;
use Prismic\Document;
use NetgluePrismic\Exception;
use NetgluePrismic\Mvc\Router\RouterOptions;
use NetgluePrismic\Mvc\LinkGenerator;
use NetgluePrismic\View\Model\DocumentViewModel;
use Prismic\LinkResolver;

class Prismic extends AbstractPlugin implements
    EventManagerAwareInterface,
    ApiAwareInterface,
    ContextAwareInterface
{

    use EventManagerAwareTrait;
    use ApiAwareTrait;
    use ContextAwareTrait;

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
     * @var LinkGenerator The link generator
     */
    private $linkGenerator;

    /**
     * Current Document
     * @var Document|null
     */
    protected $document;

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
     * Shorthand method to locate the requested doucument from parameters in the request
     *
     * Tries to locate the document from it's bookmark, otherwise, by it's id
     * and return it. Also sets the document as the 'current' document context
     * and emits an event so that other helpers can pick up on the located document
     * and do their work
     *
     * @return \Prismic\Document|null
     */
    public function getDocument()
    {
        if (!$this->document) {
            $document = null;
            if ($this->isBookmarkRequest()) {
                $document = $this->getBookmarkedDocumentFromRequest();
            }
            if ($this->isMaskRequest() && !$document) {
                $document = $this->getDocumentByMaskAndIdFromRequest();
            }
            if (!$document && $this->getDocumentIdFromRoute()) {
                $document = $this->getDocumentById($this->getDocumentIdFromRoute());
            }
            if ($document) {
                $this->setDocument($document);
            }
        }

        return $this->document;
    }

    /**
     * Sets the current requested document
     *
     * This method triggers the primary event we want to listen for but stores a
     * reference to the requested document so that the event is not triggered more
     * than once for the same document
     *
     * @param  Document $document
     * @return void
     */
    public function setDocument(Document $document)
    {
        if ($this->document !== $document) {
            $this->document = $document;
            $this->getEventManager()->trigger(__FUNCTION__, $this, array(
                'document' => $this->document,
            ));
        }
    }

    /**
     * Return a single document with the given id at the current repo ref
     * @param  string                 $id
     * @return \Prismic\Document|null
     */
    public function getDocumentById($id)
    {
        return $this->getContext()->getDocumentById($id);
    }

    /**
     * Return a single document for the given bookmark name
     * @param  string            $bookmark
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

    public function isMaskRequest()
    {
        $mask = $this->getMaskFromRoute();

        return !empty($mask);
    }

    /**
     * Return the route parameters for the given document
     * @param  Document $document
     * @return array
     * @see    NetgluePrismic\Mvc\LinkResolver::getRouteParams
     */
    public function getRouteParamsForDocument(Document $document)
    {
        $link = $this->getLinkGenerator()->generate($document);

        return $this->getLinkResolver()->getRouteParams($link);
    }

    /**
     * Return the document bookmarked in the route for the current request
     * @return Document|null
     */
    public function getBookmarkedDocumentFromRequest()
    {
        if (!$this->isBookmarkRequest()) {
            throw new Exception\RuntimeException('The request does not contain a bookmark parameter');
        }
        $bookmark = $this->getBookmarkNameFromRoute();
        $document = $this->getDocumentByBookmark($bookmark);
        if (!$document) {
            throw new Exception\RuntimeException(sprintf(
                'The bookmark %s does not reference a document',
                $bookmark
            ));
        }

        return $document;
    }

    public function getDocumentByMaskAndIdFromRequest()
    {
        if (!$this->isMaskRequest()) {
            throw new Exception\RuntimeException('The request does not contain a mask parameter');
        }

        $mask = $this->getMaskFromRoute();
        $id   = $this->getDocumentIdFromRoute();
        $uid  = $this->getDocumentUidFromRoute();

        if (empty($id) && empty($uid)) {
            throw new Exception\RuntimeException('The request contains neither document id nor a document uid, therefore a single document cannot be located');
        }
        if(!empty($id)) {
            return $this->getDocumentById($id);
        }
        return $this->getDocumentByUidAndMask($uid, $mask);
    }

    public function getDocumentByUidAndMask($uid, $mask)
    {
        return $this->getContext()->getDocumentByUidAndMask($uid, $mask);
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

    /**
     * Return the document id from the current matched route
     * @return string|NULL
     */
    public function getDocumentIdFromRoute()
    {
        $params = $this->getController()->plugin('params');
        $search = $this->routerOptions->getIdParam();

        return $params->fromRoute($search);
    }

    /**
     * Return the document UID from the current matched route
     * @return string|NULL
     */
    public function getDocumentUidFromRoute()
    {
        $params = $this->getController()->plugin('params');
        $search = $this->routerOptions->getUidParam();

        return $params->fromRoute($search);
    }

    public function createViewModel($document = NULL)
    {
        $model = new DocumentViewModel;
        $model->setLinkResolver($this->getLinkResolver());
        if (!is_null($document)) {
            if (is_string($document)) {
                $document = $this->getDocumentById($id);
            }
            if (!$document instanceof Document) {
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

    public function getLinkGenerator()
    {
        return $this->linkGenerator;
    }

    public function setLinkGenerator(LinkGenerator $linkGenerator)
    {
        $this->linkGenerator = $linkGenerator;

        return $this;
    }

}
