<?php

namespace NetgluePrismic\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Prismic\Api;

class PrismicApiClientFactory implements FactoryInterface
{

    /**
     * Return Prismic Api Client
     * @param ServiceLocatorInterface $serviceLocator
     * @return Api
     * @throws \RuntimeException
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('Config');
        if(!isset($config['prismic']['api'])) {
            throw new \RuntimeException('No configuration has been provided in order to retrieve a Prismic API Client');
        }
        $config = $config['prismic'];

        $url = $config['api'];
        $token = isset($config['token']) ? $config['token'] : NULL;

        /**
         * @see \Prismic\Api::get($apiUrl, $accesssToken, \Guzzle\Http\Client $client, \Prismic\Cache\CacheInterface $cache)
         */

        return Api::get($url, $token);
    }

}
