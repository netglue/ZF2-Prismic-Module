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
 * Controller Plugin Provider
 */
use Zend\ModuleManager\Feature\ControllerPluginProviderInterface;

/**
 * View Helper Provider
 */
use Zend\ModuleManager\Feature\ViewHelperProviderInterface;

class Module implements
             AutoloaderProviderInterface,
             ConfigProviderInterface,
             ServiceProviderInterface,
             ControllerPluginProviderInterface,
             ViewHelperProviderInterface
{

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

}
