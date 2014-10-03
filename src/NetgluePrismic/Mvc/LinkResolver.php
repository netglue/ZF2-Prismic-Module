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

    /**
     * Cached bookmark routes
     * @var array
     */
    protected $bookmarkRouteNames;

    /**
     * Cached mask based routes
     * @var array
     */
    protected $maskRouteNames;

    /**
     * Resolve the given link
     * @param LinkInterface $link
     * @return string|NULL
     */
    public function resolve($link)
    {
        if(!$link instanceof LinkInterface) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Expected an instance of LinkInterface recieved %s %s',
                gettype($link),
                (is_scalar($link) ? $link : '')
            ));
        }


        if($link instanceof DocumentLink) {

            // Is the link broken?
            if($link->isBroken()) {
                return NULL;
            }

            // Is the document a bookmark?
            if($this->getContext()->isBookmarked($link->getId())) {
                $bookmark = $this->getContext()->findBookmarkByDocument($link->getId());

                try {
                    $routeName = $this->getRouteNameFromBookmark($bookmark);
                    return $this->getRouter()->assemble($this->getRouteParams($link), array(
                        'name' => $routeName,
                    ));
                } catch(Exception\ExceptionInterface $ex) {
                    // It's perfectly acceptable that there might not be a route setup
                    // for the found bookmark.
                }
            }

            // Can we route based on the mask type
            if($this->hasRouteForMask($link->getType())) {
                $routeName = $this->getRouteNameFromMask($link->getType());

                return $this->getRouter()->assemble($this->getRouteParams($link), array(
                    'name' => $routeName,
                ));
            }

            /**
             * Other possible ways to route... ?
             *
             * It's not sensible to route by matching slugs as we have absolutely no
             * way of making any slug unique or ensuring that it exists in the first place.
             *
             * Slugs are for presenting pretty urls so providing them when building the url
             * is the only real purpose for them.
             *
             * Routing based on collection is feasible and relatively easy to implement.
             * Much the same as masks, but, any given document could be in multiple collections which makes it
             * difficult to discover the route when given a single document.
             *
             * Routing based on fragment text value has potential??
             */

            // Cannot find a specific or generic route for the document
            return NULL;
        }

        return $link->getUrl($this);
    }

    /**
     * Create Prismic specific Route Paramters from the given Document Link according to routing options
     * @param DocumentLink $link
     * @return array
     */
    public function getRouteParams(DocumentLink $link)
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
     * Return an associative array of bookmark names to route names
     * @return array
     */
    public function getBookmarkedRoutes()
    {
        if(!$this->bookmarkRouteNames) {
            $this->bookmarkRouteNames = $this->findBookmarkedRoutes($this->getRoutes());
        }
        return $this->bookmarkRouteNames;
    }

    /**
     * Search routes for those containing the prismic bookmark parameter in defaults
     *
     * @param array $routes Router config array
     * @param string $parent to help work out the fully qualified route name when called recursively
     * @return array
     */
    protected function findBookmarkedRoutes(array $routes, $parent = '')
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
                $out = array_merge($out, $this->findBookmarkedRoutes($route['child_routes'], $name));
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
        $routes = $this->getMaskRoutes($this->getRoutes());
        if(!isset($routes[$mask])) {
            throw new Exception\RuntimeException('No route could be found for the specified document mask');
        }
        return $routes[$mask];
    }

    /**
     * Whether there is a route for the given mask/type
     * @param string $mask
     * @return bool
     */
    public function hasRouteForMask($mask)
    {
        $routes = $this->getMaskRoutes($this->getRoutes());

        return isset($routes[$mask]);
    }

    /**
     * Return an associative array of mask names to route names
     * @return array
     */
    public function getMaskRoutes()
    {
        if(!$this->maskRouteNames) {
            $this->maskRouteNames = $this->findRoutesReferencedToMasks($this->getRoutes());
        }
        return $this->maskRouteNames;
    }

    /**
     * Recursively search route config to find those route names that reference a prismic mask/type
     * @param array $routes
     * @param string $parent
     * @return array
     */
    protected function findRoutesReferencedToMasks(array $routes, $parent = '')
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
                $out = array_merge($out, $this->findRoutesReferencedToMasks($route['child_routes'], $name));
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
        $this->bookmarkRouteNames = $this->maskRouteNames = NULL;
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
        $this->bookmarkRouteNames = $this->maskRouteNames = NULL;
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
