<?php
namespace NetgluePrismic\Mvc\Controller;
use NetgluePrismic\Service\Sitemap;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;


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
        return $view;
    }

    /**
     * Render an individual sitemap
     */
    public function sitemapAction()
    {

    }

    public function setSitemapService(Sitemap $sitemap)
    {
        $this->sitemaps = $sitemap;
    }

}
