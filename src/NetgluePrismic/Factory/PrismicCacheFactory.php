<?php
/**
 * Return a cache instance that implements \Prismic\Cache\CacheInterface
 */
namespace NetgluePrismic\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use Prismic\Cache\CacheInterface;

// For Zend Caches
use Zend\Cache\Storage\StorageInterface;
use NetgluePrismic\Cache\Facade;

// For Psr Caches
use Psr\Cache\CacheItemPoolInterface;
use NetgluePrismic\Cache\PsrCacheFacade;

// When all else fails…
use Prismic\Cache\NoCache;

class PrismicCacheFactory implements FactoryInterface
{

    public function createService(ServiceLocatorInterface $serviceLocator) : CacheInterface
    {
        $config = $serviceLocator->get('Config');
        $config = isset($config['prismic'])
                ? $config['prismic']
                : [];

        $cache = null;

        if (!empty($config['cache'])) {
            $cacheService = $serviceLocator->get($config['cache']);

            // If the service manager has given us a Prismic Cache, just return it
            if ($cacheService instanceof CacheInterface) {
                return $cacheService;
            }

            // Wrap Psr Caches in a facade
            if ($cacheService instanceof CacheItemPoolInterface) {
                return new PsrCacheFacade($cacheService);
            }

            // Wrap Zend Caches with a different Facade
            if ($cacheService instanceof StorageInterface) {
                return new Facade($cacheService);
            }
        }

        /**
         * Finally, if there's no cache configured, or whatever we got from
         * the service manager can't be used, return a 'NoCache'
         */
        return new NoCache;
    }
}

