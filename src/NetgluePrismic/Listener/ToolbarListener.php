<?php

namespace NetgluePrismic\Listener;

use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\ListenerAggregateTrait;
use Zend\EventManager\EventManagerInterface;
use Zend\View\Model\ViewModel;

class ToolbarListener
{

    use ListenerAggregateTrait;

    /**
     * @var object
     */
    protected $renderer;

    /**
     * Service Locator
     */
    protected $serviceLocator;

    /**
     * Constructor.
     *
     * @param object $viewRenderer
     */
    public function __construct($viewRenderer, $services)
    {
        $this->renderer = $viewRenderer;
        $this->serviceLocator = $services;
    }

    public function injectToolbar($e)
    {
        $formManager = $this->serviceLocator->get('FormElementManager');
        $select = $formManager->get('NetgluePrismic\Form\Element\SelectPrismicRef');

        $response    = $e->getApplication()->getResponse();
        $toolbarView = new ViewModel;
        $toolbarView->selectRef = $select;
        $toolbarView->setTemplate('netglue-prismic/toolbar/toolbar');
        $toolbar     = $this->renderer->render($toolbarView);

        $toolbarCss  = new ViewModel;
        $toolbarCss->setTemplate('netglue-prismic/toolbar/styles');
        $style       = $this->renderer->render($toolbarCss);

        $injected    = preg_replace('/<\/body>/i', $toolbar . "\n</body>", $response->getBody(), 1);
        $injected    = preg_replace('/<\/head>/i', $style . "\n</head>", $injected, 1);
        $response->setContent($injected);
    }


}
