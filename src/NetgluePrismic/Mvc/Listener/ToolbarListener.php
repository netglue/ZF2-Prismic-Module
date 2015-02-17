<?php
/**
 * Listener that will inject content into the response providing that the
 * shouldRender flag is true.
 * The listener is attached to an event manager in module bootstrap
 */

namespace NetgluePrismic\Mvc\Listener;

use Zend\EventManager\ListenerAggregateTrait;
use Zend\Mvc\MvcEvent;
use Zend\View\Model\ViewModel;
use Zend\View\Renderer\RendererInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ToolbarListener
{

    use ListenerAggregateTrait;

    /**
     * View Renderer
     * @var RendererInterface
     */
    protected $renderer;

    /**
     * Service Locator
     */
    protected $serviceLocator;

    /**
     * Whether we should render or not
     * @var bool
     */
    protected $shouldRender = false;

    /**
     * Constructor.
     *
     * @param RendererInterface       $viewRenderer
     * @param ServiceLocatorInterface $services
     */
    public function __construct(RendererInterface $viewRenderer, ServiceLocatorInterface $services)
    {
        $this->renderer = $viewRenderer;
        $this->serviceLocator = $services;
    }

    /**
     * Set the should render flag
     * @param  bool $flag
     * @return self
     */
    public function setShouldRender($flag = true)
    {
        $this->shouldRender = (bool) $flag;

        return $this;
    }

    /**
     * Whether the toolbar should render or not
     * @return bool
     */
    public function shouldRender()
    {
        return $this->shouldRender;
    }

    /**
     * Callback that does the work of injecting the toolbar into the response
     *
     * @param  MvcEvent $event
     * @return void
     */
    public function injectToolbar(MvcEvent $event)
    {
        if (!$this->shouldRender()) {
            return;
        }
        $formManager = $this->serviceLocator->get('FormElementManager');
        $select = $formManager->get('NetgluePrismic\Form\Element\SelectPrismicRef');
        $request     = $event->getApplication()->getRequest();
        $url         = (string) $request->getUri();
        $response    = $event->getApplication()->getResponse();

        $toolbarView            = new ViewModel;
        $toolbarView->selectRef = $select;
        $toolbarView->url       = $url;
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
