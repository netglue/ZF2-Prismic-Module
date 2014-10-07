<?php

namespace NetgluePrismic\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Prismic\Api;
use Zend\Session\Container;
use Guzzle\Http\Exception\HttpException;

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
        $token = $configToken = isset($config['token']) ? $config['token'] : NULL;

        /**
         * Check the Session for a token and prefer that if it exists.
         */
        $session = new Container('Prismic');
        if(isset($session->access_token)) {
            $token = $session->access_token;
        }

        /**
         * @see \Prismic\Api::get($apiUrl, $accesssToken, \Guzzle\Http\Client $client, \Prismic\Cache\CacheInterface $cache)
         */

        /**
         * Wrap api initialisation in a try/catch in case the temporary token we got from the session
         * has expired
         */
        try {
            $api = Api::get($url, $token);
        } catch(HttpException $e) {
            $api = Api::get($url, $configToken);
        }
        return $api;
    }

}
