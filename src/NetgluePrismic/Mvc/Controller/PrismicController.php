<?php

namespace NetgluePrismic\Mvc\Controller;

use Zend\Mvc\Controller\AbstractActionController;

use NetgluePrismic\Exception;

use Zend\Session\Container;

class PrismicController extends AbstractActionController
{

    private function requireClientIdAndSecret(&$clientId, &$clientSecret)
    {
        $clientId = $this->getClientId();
        if(empty($clientId)) {
            throw new Exception\InvalidClientIdException('No Client ID has been provided');
        }

        $clientSecret = $this->getClientSecret();
        if(empty($clientSecret)) {
            throw new Exception\InvalidClientIdException('No Client Secret has been provided');
        }
    }

    /**
     * Redirects to the oauth endpoint
     * @return void
     */
    public function signinAction()
    {
        // OAuth Initiation Endpoint
        $endpoint = $this->prismic()->api()->oauthInitiateEndpoint();

        // Make sure we have an ID/Secret
        $this->requireClientIdAndSecret($clientId, $clientSecret);

        // Construct URI to our callback for requesting an access token
        $callback = clone($this->getRequest()->getUri());
        $callback->setPath($this->url()->fromRoute('prismic-signin/callback'));

        // Redirect end-user to authorise
        $params = http_build_query(array(
            "client_id" => $clientId,
            "redirect_uri" => (string) $callback,
            "scope" => "master+releases"
        ));
        $url = $endpoint . '?' . $params;
        return $this->redirect()->toUrl($url);
    }

    public function oauthCallbackAction()
    {
        // We should have 'code' in GET params:
        $code = $this->params()->fromQuery('code');
        if(empty($code)) {
            $this->getResponse()->setReasonPhrase('Bad Request');
            $this->getResponse()->setStatusCode(404);
            return;
        }

        // Make sure we have an ID/Secret
        $this->requireClientIdAndSecret($clientId, $clientSecret);

        // Construct absolute callback URL
        // I don't know why we need this...
        $callback = clone($this->getRequest()->getUri());
        $callback->setPath($this->url()->fromRoute('prismic-signin/callback'));
        $callback->setQuery('');

        $params = array(
            "grant_type" => array('authorization_code'),
            "code" => $code,
            "redirect_uri" => (string) $callback,
            "client_id" => $clientId,
            "client_secret" => $clientSecret,
        );

        // Get the token endpoint:
        $endpoint = $this->prismic()->api()->oauthTokenEndpoint();
        // Use Curl for straightforward SSL
        $client = new \Zend\Http\Client($endpoint, array(
            'adapter' => 'Zend\Http\Client\Adapter\Curl'
        ));
        $client->setMethod('POST');
        $client->setParameterPost($params);
        $response = $client->send();

        if(!$response->isSuccess()) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        $data = json_decode($response->getBody());
        $accessToken = $data->access_token;
        $expires = $data->expires_in;

        // Take a minute from the expiry duration incase clocks are off
        $expires -= 60;

        $session = new Container('Prismic');
        $session->access_token = $accessToken;
        $session->setExpirationSeconds($expires, 'access_token');
        return $this->redirect()->toUrl('/');
    }

    protected function getClientId()
    {
        $sl = $this->getServiceLocator();
        $config = $sl->get('config');
        if(isset($config['prismic']['clientId'])) {
            return $config['prismic']['clientId'];
        }

        return null;
    }

    protected function getClientSecret()
    {
        $sl = $this->getServiceLocator();
        $config = $sl->get('config');
        if(isset($config['prismic']['clientSecret'])) {
            return $config['prismic']['clientSecret'];
        }

        return null;
    }

    public function getContext()
    {
        return $this->getServiceLocator()->get('Prismic\Context');
    }

    public function changeRefAction()
    {
        $ref = $this->params()->fromQuery('ref');
        $url = $this->params()->fromQuery('url');
        if(!empty($url)) {
            try {
                $uri = new \Zend\Uri\Uri($url);
            } catch(\Exception $e) {
            }
        }
        if(!isset($uri) || !isset($ref)) {
            return $this->getResponse()->setStatusCode(400);
        }
        $redirect = (string) $uri;

        // Make sure ref is valid
        $ref = $this->getContext()->getRefWithString($ref);
        if(!is_object($ref)) {
            return $this->getResponse()->setStatusCode(404);
        }

        // Store the ref in the session
        $session = new Container('Prismic');
        $session->ref = (string) $ref;
        $this->getContext()->getPrismicApi()->getCache()->clear();
        return $this->redirect()->toUrl($redirect);

        var_dump($ref); exit;

    }

}
