<?php

namespace NetgluePrismic\Mvc;

use Prismic\LinkResolver as PrismicResolver;

use Zend\Mvc\Router\RouteStackInterface;
use NetgluePrismic\Mvc\Router\RouterOptions;

use NetgluePrismic\Context;
use NetgluePrismic\ContextAwareInterface;

use Prismic\Fragment\Link\LinkInterface;
use Prismic\Fragment\Link\DocumentLink;


use NetgluePrismic\Exception;

class LinkResolver extends PrismicResolver implements ContextAwareInterface
{
    /**
     * RouteStackInterface instance.
     *
     * @var RouteStackInterface
     */
    protected $router;

    /**
     * The array of configured http routes
     * @var array
     */
    protected $routes;

    /**
     * Router Options
     * @var RouterOptions
     */
    protected $routerOptions;

    /**
     * Context Instance
     * @var Context
     */
    protected $prismicContext;

    public function resolve($link)
    {
        if(!$link instanceof LinkInterface) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Expected an instance of LinkInterface recieved %s',
                gettype($link) . ( is_scalar($link) ? $link : '' )
            ));
        }

        if($link instanceof DocumentLink) {

            // Is the document a bookmark?
            if($this->getContext()->isBookmarked($link->getId())) {
                $bookmark = $this->getContext()->findBookmarkByDocument($link->getId());
                $routeName = $this->getRouteNameFromBookmark($bookmark);

                return $this->getRouter()->assemble($this->getUrlParams($link), array(
                    'name' => $routeName,
                ));
            }

            // Otherwise route based on document type/mask
            $routeName = $this->getRouteNameFromMask($link->getType());

            return $this->getRouter()->assemble($this->getUrlParams($link), array(
                'name' => $routeName,
            ));
        }

        return $link->getUrl($this);
    }

    public function getUrlParams(DocumentLink $link)
    {
        $document = $this->getContext()->getDocumentById($link->getId());
        $bookmark = $this->getContext()->findBookmarkByDocument($document);
        return array(
            $this->routerOptions->getIdParam() => $link->getId(),
            $this->routerOptions->getMaskParam() => $document->getType(),
            $this->routerOptions->getBookmarkParam() => $bookmark,
            $this->routerOptions->getRefParam() => $this->getContext()->getRef(),
            $this->routerOptions->getSlugParam() => $document->getSlug(),
        );
    }

    /**
     * Return the fully qualified route name from a bookmark name
     *
     * @param string $bookmark
     * @return string
     * @throws Exception\RuntimeException if no matching route can be found
     */
    public function getRouteNameFromBookmark($bookmark)
    {
        $bookmarks = $this->getBookmarkedRoutes($this->getRoutes());
        if(!isset($bookmarks[$bookmark])) {
            throw new Exception\RuntimeException(sprintf(
                'No route could be found for the bookmark "%s"',
                $bookmark
            ));
        }
        return $bookmarks[$bookmark];
    }

    /**
     * Search routes for those containing the prismic bookmark parameter in defaults
     *
     * @param array $routes Router config array
     * @param string $parent to help work out the fully qualified route name when called recursively
     * @return array
     */
    protected function getBookmarkedRoutes(array $routes, $parent = '')
    {
        $searchParam = $this->routerOptions->getBookmarkParam();
        $out = array();
        foreach($routes as $name => $route) {
            $fqrn = trim($parent . '/' . $name, '/');
            $bookmark = isset($route['options']['defaults'][$searchParam]) ? $route['options']['defaults'][$searchParam] : NULL;
            if(!empty($bookmark)) {
                $out[$bookmark] = $fqrn;
            }
            if(isset($route['child_routes']) && count($route['child_routes'])) {
                $out = array_merge($out, $this->getBookmarkedRoutes($route['child_routes'], $name));
            }
        }
        return $out;
    }

    /**
     * Return the last route name that matches the given type of document
     * @param string $mask
     * @return string|NULL
     */
    public function getRouteNameFromMask($mask)
    {
        $routes = $this->getRoutesReferencedToMasks($this->getRoutes());
        if(!isset($routes[$mask])) {
            throw new Exception\RuntimeException('No route could be found for the specified document mask');
        }
        return $routes[$mask];
    }

    protected function getRoutesReferencedToMasks(array $routes, $parent = '')
    {
        $searchParam = $this->routerOptions->getMaskParam();
        $out = array();
        foreach($routes as $name => $route) {
            $fqrn = trim($parent . '/' . $name, '/');
            $maskName = isset($route['options']['defaults'][$searchParam]) ? $route['options']['defaults'][$searchParam] : NULL;
            if(!empty($maskName)) {
                $out[$maskName] = $fqrn;
            }
            if(isset($route['child_routes']) && count($route['child_routes'])) {
                $out = array_merge($out, $this->getRoutesReferencedToMasks($route['child_routes'], $name));
            }
        }
        return $out;
    }

    /**
     * Set the router to use for assembling.
     *
     * @param RouteStackInterface $router
     * @return void
     */
    public function setRouter(RouteStackInterface $router)
    {
        $this->router = $router;
    }

    /**
     * Return the application's router
     * @return RouteStackInterface|NULL
     */
    public function getRouter()
    {
        return $this->router;
    }

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
     * Set Router Options so we can figure out parameter names that correspond to the request/route
     * @param RouterOptions $options
     */
    public function setRouterOptions(RouterOptions $options)
    {
        $this->routerOptions = $options;
    }

    /**
     * Provide the configured routes.
     *
     * It's nigh on impossible to interrogate routes from the router object
     * because there are no interface methods to retrieve child routes, or get route default params etc
     * @param array $routes
     * @return void
     */
    public function setRoutes(array $routes)
    {
        $this->routes = $routes;
    }

    /**
     * Return the injected router configuration array
     * @return array
     */
    public function getRoutes()
    {
        return $this->routes;
    }
}
