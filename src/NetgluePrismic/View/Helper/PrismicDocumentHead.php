<?php

namespace NetgluePrismic\View\Helper;

use Zend\View\Helper\AbstractHelper;

use Prismic\Document;
use Prismic\Fragment\Image as ImageFragment;

/**
 * View helper tries to find named fragments that correspond to common page meta
 * and provides this data to various framework view helpers in an effort to
 * reduce the amount of work required to set this data for many different types of mask.
 *
 * All you have to do is consistently name these properties in your prismic masks,
 * configure those names and call the helper with the current document
 */

class PrismicDocumentHead extends AbstractHelper
{
    /**
     * @var Document
     */
    protected $document;

    /**
     * @var array
     */
    protected $propertyMap = array(
        'title' => 'meta_title',
        'description' => 'meta_description',
        'ogImage' => 'og_image',
        'ogTitle' => 'meta_title',
        'ogDescription' => 'meta_description',
    );

    protected $options = array(

    );


    public function __invoke(Document $document = NULL)
    {
        if($document) {
            $this->setDocument($document);
        }

        return $this;
    }

    public function setDocument(Document $document)
    {
        $this->document = $document;

        // Set all head values immediately

        $this->setMetaTitle();
        $this->setMetaDescription();
        $this->setOgTitle();
        $this->setOgDescription();
        $this->setOgImage();

        return $this;
    }

    public function getDocument()
    {
        return $this->document;
    }

    protected function localPropertyToDocumentProperty($property)
    {
        $docProp = $this->propertyMap[$property];
        $type = $this->document->getType();
        return sprintf('%s.%s', $type, $docProp);
    }

    protected function getTextValue($property)
    {
        $docProp = $this->localPropertyToDocumentProperty($property);
        if($this->document->has($docProp)) {
            $fragment = $this->document->get($docProp);
            if(method_exists($fragment, 'asText')) {
                return $fragment->asText();
            }
        }
        return NULL;
    }

    protected function getImageUrl($property)
    {
        $docProp = $this->localPropertyToDocumentProperty($property);
        if($this->document->has($docProp)) {
            $fragment = $this->document->get($docProp);
            if($fragment instanceof ImageFragment) {
                // Get the url of 'main'
                return $fragment->getMain()->getUrl();
            }
        }
        return NULL;
    }

    public function getMetaTitle()
    {
        return $this->getTextValue('title');
    }

    protected function setMetaTitle()
    {
        $value = $this->getMetaTitle();
        if(!empty($value)) {
            $this->getView()->headTitle($value);
        }

        return $this;
    }

    public function getMetaDescription()
    {
        return $this->getTextValue('description');
    }

    protected function setMetaDescription()
    {
        $value = $this->getMetaDescription();
        if(!empty($value)) {
            $this->getView()->headMeta()->setName('description', $value);
        }

        return $this;
    }

    public function getOgTitle()
    {
        return $this->getTextValue('ogTitle');
    }

    protected function setOgTitle()
    {
        $value = $this->getOgTitle();
        if(!empty($value)) {
            $this->getView()->headMeta()->setProperty('og:title', $value);
        }

        return $this;
    }

    public function getOgDescription()
    {
        return $this->getTextValue('ogDescription');
    }

    protected function setOgDescription()
    {
        $value = $this->getOgDescription();
        if(!empty($value)) {
            $this->getView()->headMeta()->setProperty('og:description', $value);
        }

        return $this;
    }

    public function getOgImage()
    {
        return $this->getImageUrl('ogImage');
    }

    protected function setOgImage()
    {
        $value = $this->getOgImage();
        if(!empty($value)) {
            $this->getView()->headMeta()->setProperty('og:image', $value);
        }

        return $this;
    }
}
