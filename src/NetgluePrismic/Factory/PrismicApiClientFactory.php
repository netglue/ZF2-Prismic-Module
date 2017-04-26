<?php

namespace NetgluePrismic\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Prismic\Api;
use NetgluePrismic\Session\PrismicContainer;
use GuzzleHttp\Exception\ClientException;
use Prismic\Cache\CacheInterface;
use NetgluePrismic\Cache\Facade;

class PrismicApiClientFactory implements FactoryInterface
{

    /**
     * Return Prismic Api Client
     * @param  ServiceLocatorInterface $serviceLocator
     * @return Api
     * @throws \RuntimeException
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('Config');
        if (!isset($config['prismic']['api'])) {
            throw new \RuntimeException('No configuration has been provided in order to retrieve a Prismic API Client');
        }
        $config = $config['prismic'];

        $httpClient = $cache = null;

        $url = $config['api'];
        $token = $configToken = isset($config['token']) ? $config['token'] : null;

        /**
         * Check the Session for a token and prefer that if it exists.
         * We cannot retrieve the Prismic Container from the service manager
         * because it depends on the Context, so we'd have a circular dependency
         */
        $session = new PrismicContainer('Prismic');
        if ($session->hasAccessToken()) {
            $token = $session->getAccessToken();
        }

        /**
         * See if a cache service name has been provided and wrap it in the facade
         * if required
         */
        if(!empty($config['cache'])) {
            $storage = $serviceLocator->get($config['cache']);
            if( ! $storage instanceof CacheInterface) {
                $cache = new Facade($storage);
            } else {
                $cache = $storage;
            }
        }

        if(!empty($config['httpClient'])) {
            $httpClient = $serviceLocator->get($config['httpClient']);
        }

        /**
         * @see \Prismic\Api::get($apiUrl, $accesssToken, \Guzzle\Http\Client $client, \Prismic\Cache\CacheInterface $cache)
         */

        /**
         * Wrap api initialisation in a try/catch in case the temporary token we got from the session
         * has expired
         */
        try {
            $api = Api::get($url, $token, $httpClient, $cache);
        } catch (ClientException $e) {
            $api = Api::get($url, $configToken, $httpClient, $cache);
        }

        return $api;
    }

}
