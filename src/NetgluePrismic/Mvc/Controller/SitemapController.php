<?php
namespace NetgluePrismic\Mvc\Controller;
use NetgluePrismic\Service\Sitemap;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Zend\Http\Response as HttpResponse;
use Zend\Mvc\Application;

class SitemapController extends AbstractActionController
{

    private $sitemaps;

    /**
     * Render the sitemap index
     *
     */
    public function indexAction()
    {
        $names = $this->sitemaps->getSitemapNames();
        $urls = array();
        foreach($names as $name) {
            $urls[] = $this->url()->fromRoute('prismic-sitemap/container', array('name' => $name));
        }
        
        $view = new ViewModel;
        $view->sitemaps = $urls;
        $view->setTerminal(true);
        $this->getResponse()->getHeaders()->addHeaderLine('Content-Type', 'text/xml; charset=utf-8');
        return $view;
    }

    /**
     * Render an individual sitemap
     */
    public function sitemapAction()
    {
        $name = $this->params()->fromRoute('name');
        $container = $this->sitemaps->getContainerByName($name);
        if (! $container) {
            return $this->raise404();
        }
        
        $view = new ViewModel;
        $view->container = $container;
        $view->setTerminal(true);
        $this->getResponse()->getHeaders()->addHeaderLine('Content-Type', 'text/xml; charset=utf-8');
        return $view;
    }

    public function setSitemapService(Sitemap $sitemap)
    {
        $this->sitemaps = $sitemap;
    }
    
    /**
     * Set the response to a 404 error
     */
    protected function raise404()
    {
        $e = $this->getEvent();
        $e->setError(Application::ERROR_CONTROLLER_INVALID);
        $response = $e->getResponse();
        if ($response instanceof HttpResponse) {
            $response->setStatusCode(404);
        }
    }

}
