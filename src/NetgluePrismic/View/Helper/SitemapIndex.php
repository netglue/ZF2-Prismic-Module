<?php

namespace NetgluePrismic\View\Helper;

use Zend\View\Helper\AbstractHelper;
use DOMDocument;
use NetgluePrismic\Exception;

/**
 * Shameless copy/paste from \Zend\View\Helper\Navigation\Sitemap
 */
class SitemapIndex extends AbstractHelper
{
    /**
     * Namespace for the <sitemapindex> tag
     *
     * @var string
     */
    const SITEMAP_NS = 'http://www.sitemaps.org/schemas/sitemap/0.9';

    const SITEMAP_XSD = 'http://www.sitemaps.org/schemas/sitemap/0.9/siteindex.xsd';

    /**
     * Whether XML output should be formatted
     *
     * @var bool
     */
    protected $formatOutput = false;

    /**
     * List of sitemap URLs
     * @var array
     */
    protected $urls = array();

    /**
     * Absolute Server URL
     * @var string
     */
    protected $serverUrl;

    /**
     * @param  array $urls
     * @return self
     */
    public function __invoke($urls = null)
    {
        if (null !== $urls) {
            $this->setUrls($urls);
        }

        return $this;
    }

    /**
     * Set Sitemap URLs
     * @param  array $urls
     * @return self
     */
    public function setUrls(array $urls)
    {
        $this->urls = array();
        foreach ($urls as $data) {
            if (is_string($data)) {
                $this->addUrl($data);
            } elseif (is_array($data)) {
                $url = current($data);
                $lastmod = end($data);
                $this->addUrl($url, $lastmod);
            }
        }

        return $this;
    }

    /**
     * Return the array of URLs
     * @return array
     */
    public function getUrls()
    {
        return $this->urls;
    }

    /**
     * Add a url with an optional lastmod property
     * @param  string                             $url
     * @param  string                             $lastmod
     * @return self
     * @throws Exception\InvalidArgumentException
     */
    public function addUrl($url, $lastmod = null)
    {
        if (!is_string($url)) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s expects a string for the URL. Received %s',
                __FUNCTION__,
                gettype($url)));
        }
        if ( (null !== $lastmod) && !is_string($lastmod) ) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s Currently only accepts strings for the lastmod property. Received %s',
                __FUNCTION__,
                gettype($lastmod)));
        }
        $this->urls[] = array(
            'url'     => $url,
            'lastmod' => $lastmod,
        );

        return $this;
    }

    /**
     * Render XML
     * @return string
     */
    public function render()
    {
        $dom = $this->getDomSitemap();
        $xml = $dom->saveXML();

        return rtrim($xml, PHP_EOL);
    }

    /**
     * Return the sitemap as DOMDocument
     * @return DOMDocument
     */
    public function getDomSitemap()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = $this->getFormatOutput();

        $index = $dom->createElementNS(self::SITEMAP_NS, 'sitemapindex');
        $dom->appendChild($index);

        foreach ($this->urls as $data) {
            if (!$url = $this->url($data['url'])) {
                continue;
            }
            $sitemapNode = $dom->createElementNS(self::SITEMAP_NS, 'sitemap');
            $index->appendChild($sitemapNode);
            $sitemapNode->appendChild($dom->createElementNS(self::SITEMAP_NS, 'loc', $url));
            if (isset($data['lastmod'])) {
                // Currently lastmod is expected to be a valid date time string
                $sitemapNode->appendChild($dom->createElementNS(self::SITEMAP_NS, 'lastmod', $data['lastmod']));
            }
        }

        // Maybe validate schema just like in \Zend\View\Helper\Navigation\Sitemap

        return $dom;
    }

    /**
     * Returns an escaped absolute URL for the given url
     *
     * @param  string      $url
     * @return string|null
     */
    public function url($url)
    {
        if (!isset($url{0})) {
            return null;
        } elseif ($url{0} == '/') {
            // href is relative to root; use serverUrl helper
            $url = $this->getServerUrl() . $url;
        } elseif (preg_match('/^[a-z]+:/im', (string) $url)) {
            // scheme is given in href; assume absolute URL already
            $url = (string) $url;
        } else {
            return null;
        }

        return $this->xmlEscape($url);
    }

    /**
     * Escapes string for XML usage
     *
     * @param  string $string
     * @return string
     */
    protected function xmlEscape($string)
    {
        $escaper = $this->view->plugin('escapeHtml');

        return $escaper($string);
    }

    /**
     * Returns server URL
     *
     * @return string
     */
    public function getServerUrl()
    {
        if (!isset($this->serverUrl)) {
            $serverUrlHelper  = $this->getView()->plugin('serverUrl');
            $this->serverUrl = $serverUrlHelper();
        }

        return $this->serverUrl;
    }

    /**
     * Sets whether XML output should be formatted
     *
     * @param  bool $formatOutput
     * @return self
     */
    public function setFormatOutput($formatOutput = true)
    {
        $this->formatOutput = (bool) $formatOutput;

        return $this;
    }

    /**
     * Returns whether XML output should be formatted
     *
     * @return bool
     */
    public function getFormatOutput()
    {
        return $this->formatOutput;
    }

}
