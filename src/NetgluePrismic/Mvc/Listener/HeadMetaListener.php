<?php

/**
 * This listener keeps a reference to the MVC event as it needs to get hold
 * of the view, additionally it listens for events triggered by the prismic
 * controller plugin, along with configuration, to decide whether to apply
 * field values found in documents to the presentation layer automatically
 */

namespace NetgluePrismic\Mvc\Listener;

use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\ListenerAggregateTrait;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\EventInterface;
use Zend\View\HelperPluginManager;
use Prismic\Fragment\Image as ImageFragment;

class HeadMetaListener implements ListenerAggregateInterface
{

    use ListenerAggregateTrait;

    /**
     * @var HelperPluginManager View Helper Plugin Manager
     */
    protected $helperManager;

    /**
     * @var array An array of document field names to look for
     */
    protected $options = array(
        'enabled' => false,
        'propertyMap' => array(
            'title' => 'meta_title',
            'description' => 'meta_description',
            'ogImage' => 'og_image',
            'ogTitle' => 'meta_title',
            'ogDescription' => 'meta_description',
        ),
    );

    /**
     * @var Prismic\Document
     */
    protected $document;

    /**
     * Construct the listener. Requires the view helper plugin manager
     * @param  HelperPluginManager $helperManager
     */
    public function __construct(HelperPluginManager $helperManager)
    {
        $this->helperManager = $helperManager;
    }

    /**
     * Set Options
     *
     * Currently, there is no enforcement/checks for given options, so there
     * will be unexpected errors if you provide anything unexpected here.
     *
     * @TODO   Fix this to accept a strict options object using Zend\Stdlib\AbstractOptions
     * @param  array $options
     * @return self
     */
    public function setOptions($options)
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Get Options
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Set the enabled flag
     * @param  bool $flag
     * @return self
     */
    public function setEnabled($flag = true)
    {
        $this->options['enabled'] = (bool) $flag;

        return $this;
    }

    /**
     * Whether the listener action is enabled or not
     * @return bool
     */
    public function enabled()
    {
        return isset($this->options['enabled']) ? (bool) $this->options['enabled'] : false;
    }

    /**
     * Attach to specific events with the shared manager
     * @param  EventManagerInterface $events
     * @return void
     */
    public function attach(EventManagerInterface $events)
    {
        $shared = $events->getSharedManager();
        $this->listeners[] = $shared->attach('NetgluePrismic\Mvc\Controller\Plugin\Prismic', 'setDocument', array($this, 'onSetDocument'));
    }

    /**
     * Document is Set Callback
     * @param  EventInterface $event
     * @return void
     */
    public function onSetDocument(EventInterface $event)
    {
        if (!$this->options['enabled']) {
            return;
        }
        $this->document = $event->getParam('document');
        $this->setMetaTitle();
        $this->setMetaDescription();
        if ($this->doctype()->isRdfa()) {
            $this->setOgTitle();
            $this->setOgDescription();
            $this->setOgImage();
        }
    }

    /**
     * Return the fully qualified document field name given a property name/key
     * i.e. given 'title', expect 'my_document_type.meta_title' or similar
     *
     * @param  string $property
     * @return string
     */
    protected function localPropertyToDocumentProperty($property)
    {
        $docProp = isset($this->options['propertyMap'][$property]) ? $this->options['propertyMap'][$property] : $property;
        $type = $this->document->getType();

        return sprintf('%s.%s', $type, $docProp);
    }

    /**
     * Return the text value of the given document property
     * @param  string      $property A property name that maps to the expected field value in a document
     * @return string|null
     */
    public function getTextValue($property)
    {
        $docProp = $this->localPropertyToDocumentProperty($property);
        if ($this->document->has($docProp)) {
            $fragment = $this->document->get($docProp);

            return $fragment->asText();
        }

        return null;
    }

    /**
     * Assuming the given property is for an image, return the URL for the 'main' image in that fragment
     * @param  string      $property A local property name
     * @return string|null
     */
    public function getImageUrl($property)
    {
        $docProp = $this->localPropertyToDocumentProperty($property);
        if ($this->document->has($docProp)) {
            $fragment = $this->document->get($docProp);
            if ($fragment instanceof ImageFragment) {

                // Get the url of 'main'
                return $fragment->getMain()->getUrl();
            }
        }

        return null;
    }

    /**
     * Return HeadMeta View Helper
     * @return \Zend\View\Helper\HeadTitle
     */
    public function headTitle()
    {
        return $this->helperManager->get('headtitle');
    }

    /**
     * Return HeadMeta View Helper
     * @return \Zend\View\Helper\HeadMeta
     */
    public function headMeta()
    {
        return $this->helperManager->get('headmeta');
    }

    /**
     * Return the doctype view helper
     * @return Zend\View\Helper\Doctype
     */
    public function doctype()
    {
        return $this->helperManager->get('doctype');
    }

    /**
     * Set the Meta Title using the HeadTitle plugin if non-empty
     * @return self
     */
    protected function setMetaTitle()
    {
        $value = $this->getTextValue('title');
        if (!empty($value)) {
            $this->helperManager->get('headtitle')->set($value);
        }

        return $this;
    }

    /**
     * Set the Meta Description using the HeadMeta plugin if non-empty
     * @return self
     */
    protected function setMetaDescription()
    {
        $value = $this->getTextValue('description');
        if (!empty($value)) {
            $this->headMeta()->setName('description', $value);
        }

        return $this;
    }

    /**
     * Set the Open Graph Title using the HeadMeta plugin if non-empty
     * @return self
     */
    protected function setOgTitle()
    {
        $value = $this->getTextValue('ogTitle');
        if (!empty($value)) {
            $this->headMeta()->setProperty('og:title', $value);
        }

        return $this;
    }

    /**
     * Set the Open Graph Description using the HeadMeta plugin if non-empty
     * @return self
     */
    protected function setOgDescription()
    {
        $value = $this->getTextValue('ogDescription');
        if (!empty($value)) {
            $this->headMeta()->setProperty('og:description', $value);
        }

        return $this;
    }

    protected function setOgImage()
    {
        $value = $this->getImageUrl('ogImage');
        if (!empty($value)) {
            $this->headMeta()->setProperty('og:image', $value);
        }

        return $this;
    }

}
