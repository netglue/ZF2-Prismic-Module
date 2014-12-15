<?php

namespace NetgluePrismic;

/**
 * Autoloader
 */
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\Loader\AutoloaderFactory;
use Zend\Loader\StandardAutoloader;

/**
 * Config Provider
 */
use Zend\ModuleManager\Feature\ConfigProviderInterface;

/**
 * Service Provider
 */
use Zend\ModuleManager\Feature\ServiceProviderInterface;

/**
 * Controller Provider
 */
use Zend\ModuleManager\Feature\ControllerProviderInterface;

/**
 * Controller Plugin Provider
 */
use Zend\ModuleManager\Feature\ControllerPluginProviderInterface;

/**
 * View Helper Provider
 */
use Zend\ModuleManager\Feature\ViewHelperProviderInterface;

/**
 * Form Element Provider
 */
use Zend\ModuleManager\Feature\FormElementProviderInterface;

/**
 * Bootstrap Listener
 */
use Zend\ModuleManager\Feature\BootstrapListenerInterface;
use Zend\EventManager\EventInterface;

/**
 * @codeCoverageIgnore
 */
class Module implements
             AutoloaderProviderInterface,
             ConfigProviderInterface,
             ServiceProviderInterface,
             ControllerProviderInterface,
             ControllerPluginProviderInterface,
             ViewHelperProviderInterface,
             BootstrapListenerInterface,
             FormElementProviderInterface
{

    /**
     * Listen to the bootstrap event
     *
     * @param  EventInterface $e
     * @return array
     */
    public function onBootstrap(EventInterface $e)
    {
        /**
         * For now, tests die completely due to the view helper manager not being
         * available in the view manager
         */
        if (PHP_SAPI === 'cli') {
            return;
        }

        $app = $e->getApplication();
        $services = $app->getServiceManager();

        /**
         * Make sure that the session is initialised early on as this
         * is where we decide which ref/release to view.
         * If the document is located before the session is initialised,
         * we'll always end up looking at the master ref.
         */
        $session = $services->get('NetgluePrismic\Session\PrismicContainer');

        // Cache Buster that's triggered when we receive a valid webhook payload from the Prismic API
        $listener = $services->get('NetgluePrismic\Mvc\Listener\CacheBusterListener');
        $app->getEventManager()->attach($listener);

        // Listener to automatically set head meta tags etc.
        $listener = $services->get('NetgluePrismic\Mvc\Listener\HeadMetaListener');
        $app->getEventManager()->attach($listener);

        // Listener that provides the current document to the prismic view helper
        $listener = $services->get('NetgluePrismic\Mvc\Listener\ViewHelperDocumentListener');
        $app->getEventManager()->attach($listener);

        // Listener to inject a toolbar into the view
        $app->getEventManager()->attach(\Zend\Mvc\MvcEvent::EVENT_FINISH, array(
            $services->get('NetgluePrismic\Mvc\Listener\ToolbarListener'),
            'injectToolbar'
        ));
    }

    /**
     * Return autoloader configuration
     * @link http://framework.zend.com/manual/2.0/en/user-guide/modules.html
     * @return array
     */
    public function getAutoloaderConfig()
    {
        return array(
            AutoloaderFactory::STANDARD_AUTOLOADER => array(
                StandardAutoloader::LOAD_NS => array(
                    __NAMESPACE__ => __DIR__,
                ),
            ),
        );
    }

    /**
     * Return Module Configuration
     * @return array
     */
    public function getConfig()
    {
        return include __DIR__ . '/../../config/module.config.php';
    }

    /**
     * Return Service Config
     * @return array
     * @implements ServiceProviderInterface
     */
    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                // Prismic SDK Api \Prismic\Api
                'Prismic\Api' => 'NetgluePrismic\Factory\PrismicApiClientFactory',
                // Site-wide context \NetgluePrismic\Context
                'NetgluePrismic\Context' => 'NetgluePrismic\Factory\ContextFactory',
                // Options for the router/link resolver
                'NetgluePrismic\Mvc\Router\RouterOptions' => 'NetgluePrismic\Mvc\Service\RouterOptionsFactory',
                // Link Resolver
                'NetgluePrismic\Mvc\LinkResolver' => 'NetgluePrismic\Mvc\Service\LinkResolverFactory',
                'NetgluePrismic\Mvc\LinkGenerator' => 'NetgluePrismic\Mvc\Service\LinkGeneratorFactory',
                // Session for storing access tokens and selected ref/release
                'NetgluePrismic\Session\PrismicContainer' => 'NetgluePrismic\Session\ContainerFactory',
                // Service to return a NoCache instance to effectively disable caching
                'NetgluePrismic\Cache\Disable' => 'NetgluePrismic\Factory\NoCacheFactory',

                /**
                 * Listeners
                 */
                // Automatically set meta title etc when successfully routed to a single document
                'NetgluePrismic\Mvc\Listener\HeadMetaListener' => 'NetgluePrismic\Mvc\Service\HeadMetaListenerFactory',
                // Injects the routed document into the view helper
                'NetgluePrismic\Mvc\Listener\ViewHelperDocumentListener' => 'NetgluePrismic\Mvc\Service\ViewHelperDocumentListenerFactory',
                // Listener to inject the toolbar
                'NetgluePrismic\MvcListener\ToolbarListener' => function($sm) {
                    return new \NetgluePrismic\Mvc\Listener\ToolbarListener($sm->get('ViewRenderer'), $sm);
                }
            ),
            'invokables' => array(
                'NetgluePrismic\Mvc\Listener\CacheBusterListener' => 'NetgluePrismic\Mvc\Listener\CacheBusterListener',
            ),
            'aliases' => array(
                'PrismicApiClient' => 'Prismic\Api',
                'PrismicRouterOptions' => 'NetgluePrismic\Mvc\Router\RouterOptions',
                'Prismic\Context' => 'NetgluePrismic\Context'
            ),
        );
    }

    /**
     * Return Controller Config
     * @return array
     */
    public function getControllerConfig()
    {
        return array(
            'factories' => array(
                'NetgluePrismic\Mvc\Controller\PrismicController' => 'NetgluePrismic\Mvc\Service\PrismicControllerFactory',
            ),
        );
    }

    /**
     * Return controller plugin config
     * @return array
     * @implements ControllerPluginProviderInterface
     */
    public function getControllerPluginConfig()
    {
        return array(
            'factories' => array(
                'NetgluePrismic\Mvc\Controller\Plugin\Prismic' => 'NetgluePrismic\Mvc\Service\PrismicControllerPluginFactory',
                'NetgluePrismic\Mvc\Controller\Plugin\Url'     => 'NetgluePrismic\Mvc\Service\UrlControllerPluginFactory',
            ),
            'aliases' => array(
                'Prismic'       => 'NetgluePrismic\Mvc\Controller\Plugin\Prismic',
                'prismicUrl'    => 'NetgluePrismic\Mvc\Controller\Plugin\Url',
            ),
        );
    }

    /**
     * Return view helper config
     * @return array
     * @implements ViewHelperProviderInterface
     */
    public function getViewHelperConfig()
    {
        return array(
            'invokables' => array(
                'NetgluePrismic\View\Helper\EditAtPrismic' => 'NetgluePrismic\View\Helper\EditAtPrismic',
            ),
            'factories' => array(
                'NetgluePrismic\View\Helper\Prismic' => 'NetgluePrismic\View\Service\PrismicViewHelperFactory',
                'NetgluePrismic\View\Helper\Url'     => 'NetgluePrismic\View\Service\UrlViewHelperFactory',
                'NetgluePrismic\View\Helper\Finder'  => 'NetgluePrismic\View\Service\FinderViewHelperFactory',
            ),
            'aliases' => array(
                'prismic'       => 'NetgluePrismic\View\Helper\Prismic',
                'prismicUrl'    => 'NetgluePrismic\View\Helper\Url',
                'editAtPrismic' => 'NetgluePrismic\View\Helper\EditAtPrismic',
                'prismicFinder' => 'NetgluePrismic\View\Helper\Finder',
            ),
        );
    }

    /**
     * Return form element config
     * @return array
     * @implements FormElementProviderInterface
     */
    public function getFormElementConfig()
    {
        return array(
            'factories' => array(
                'NetgluePrismic\Form\Element\SelectPrismicRef' => function ($manager) {
                    $element = new \Zend\Form\Element\Select('ref');
                    $services = $manager->getServiceLocator();
                    $context = $services->get('Prismic\Context');
                    $api = $context->getPrismicApi();
                    foreach ($api->refs() as $ref) {
                        if ($ref->isMasterRef()) {
                            $options[$ref->getRef()] = 'Current Live Website';
                        } else {
                            $options[$ref->getRef()] = $ref->getLabel();
                        }
                    }
                    $element->setValueOptions($options);
                    $element->setValue( (string) $context->getRef());

                    return $element;
                }
            ),
        );
    }
}
