<?php

namespace NetgluePrismic\View\Helper;

use Zend\View\Helper\AbstractHelper;
use DOMDocument;
class SitemapIndex extends AbstractHelper
{
    /**
     * Namespace for the <sitemapindex> tag
     *
     * @var string
     */
    const SITEMAP_NS = 'http://www.sitemaps.org/schemas/sitemap/0.9';

    /**
     * Whether XML output should be formatted
     *
     * @var bool
     */
    protected $formatOutput = false;

    protected $urls = array();

    public function __invoke($urls = null) {
        if(null !== $urls) {
            $this->setUrls($urls);
        }

        return $this;
    }

    public function setUrls(array $urls)
    {
        $this->urls = array();
        foreach($urls as $data) {
            if(is_string($data)) {
                $this->addUrl($data);
            } elseif(is_array($data)) {
                $this->addUrl(current($data), end($data));
            }
        }

        return $this;
    }

    public function addUrl($url, $lastmod = null) {
        $this->urls[] = array(
            'url'     => $url,
            'lastmod' => $lastmod,
        );

        return $this;
    }

    public function render()
    {
        $dom = $this->getDomSitemap();
        $xml = $dom->saveXML();
        return rtrim($xml, PHP_EOL);
    }

    public function getDomSitemap()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = $this->getFormatOutput();

        $index = $dom->createElementNS(self::SITEMAP_NS, 'sitemapindex');
        $dom->appendChild($index);

        foreach($this->urls as $data) {
            if (!$url = $this->url($data['url'])) {
                continue;
            }
            $sitemapNode = $dom->createElementNS(self::SITEMAP_NS, 'sitemap');
            $index->appendChild($sitemapNode);
            $sitemapNode->appendChild($dom->createElementNS(self::SITEMAP_NS, 'loc', $url));
            if(isset($data['lastmod'])) {
                // Convert whatever lastmod is to date('c') and add node to $sitemapNode
            }
        }

        // Maybe validate schema
        return $dom;
    }

    /**
     * Returns an escaped absolute URL for the given url
     *
     * @param  string $url
     * @return string|null
     */
    public function url($url)
    {
        if (!isset($url{0})) {
            return null;
        } elseif ($url{0} == '/') {
            // href is relative to root; use serverUrl helper
            $url = $this->getServerUrl() . $url;
        } elseif (preg_match('/^[a-z]+:/im', (string) $href)) {
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
     * @return Sitemap
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
