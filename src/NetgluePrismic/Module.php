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

class Module implements
             AutoloaderProviderInterface,
             ConfigProviderInterface,
             ServiceProviderInterface,
             ControllerProviderInterface,
             ControllerPluginProviderInterface,
             ViewHelperProviderInterface,
             BootstrapListenerInterface
{

    /**
     * Listen to the bootstrap event
     *
     * @param EventInterface $e
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
		return include __DIR__ . '/../../config/services.config.php';
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
        return include __DIR__ . '/../../config/controller-plugins.config.php';
    }

    /**
     * Return view helper config
     * @return array
     * @implements ViewHelperProviderInterface
     */
    public function getViewHelperConfig()
    {
        return include __DIR__ . '/../../config/view-helpers.config.php';
    }

    public function getFormElementConfig()
    {
        return array(
            'factories' => array(
                'NetgluePrismic\Form\Element\SelectPrismicRef' => function($manager) {
                    $element = new \Zend\Form\Element\Select('ref');
                    $services = $manager->getServiceLocator();
                    $context = $services->get('Prismic\Context');
                    $api = $context->getPrismicApi();
                    foreach($api->refs() as $ref) {
                        if($ref->isMasterRef()) {
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
