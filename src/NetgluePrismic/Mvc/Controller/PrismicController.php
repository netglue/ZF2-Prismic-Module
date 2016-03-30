<?php

namespace NetgluePrismic\Mvc\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use NetgluePrismic\Exception;
use Zend\Session\Container;
use Zend\Http\Client as HttpClient;
use Zend\Http\Header\SetCookie;
use Zend\Http\Response as HttpResponse;
use Zend\Mvc\Application;
use Prismic\Api;

class PrismicController extends AbstractActionController
{
    /**
     * @var HttpClient|null
     */
    protected $httpClient;

    /**
     * @var Container|null
     */
    protected $session;

    /**
     * @var string Webhook Secret
     */
    protected $webhookSecret;

    /**
     * Throw an exception if we cannot retrieve the client id or secret from config
     * @param  string                                &$clientId     Populated with client ID if it has been set
     * @param  string                                &$clientSecret Populated with client Secret if it has been set
     * @return void
     * @throws Exception\InvalidCredentialsException
     */
    private function requireClientIdAndSecret(&$clientId, &$clientSecret)
    {
        $clientId = $this->getClientId();
        if (empty($clientId)) {
            throw new Exception\InvalidCredentialsException('No Client ID has been provided');
        }

        $clientSecret = $this->getClientSecret();
        if (empty($clientSecret)) {
            throw new Exception\InvalidCredentialsException('No Client Secret has been provided');
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

    /**
     * Given a valid code, post it to the token endpoint with the client secret to exchange it for a temporary access token
     * @return void
     */
    public function oauthCallbackAction()
    {
        // We should have 'code' in GET params:
        $code = $this->params()->fromQuery('code');
        if (empty($code)) {
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
        $client = $this->getHttpClient();
        $client->setUri($endpoint);
        $client->setParameterPost($params);
        $response = $client->send();

        if (!$response->isSuccess()) {
            $this->getResponse()->setStatusCode(404);

            return;
        }

        $data = json_decode($response->getBody());
        $accessToken = $data->access_token;
        $expires = $data->expires_in;

        // Take a minute from the expiry duration incase clocks are off
        $expires -= 60;

        $session = $this->getSessionContainer();
        $session->setAccessToken($accessToken);
        $session->setExpirationSeconds($expires, 'access_token');

        return $this->redirect()->toUrl('/');
    }

    /**
     * Sets a cookie for previewing draft documents
     * @return void
     */
    public function previewAction()
    {
        $token = $this->params()->fromQuery('token');
        if (empty($token)) {
            $this->getResponse()->setReasonPhrase('Bad Request');
            $this->raise404();

            return;
        }

        $api = $this->prismic()->api();
        $token = urldecode($token);

        $url = $api->previewSession($token, $this->prismic()->getLinkResolver(), '/');

        $expires = time() + (29 * 60);
        $cookie = new SetCookie(Api::PREVIEW_COOKIE, $token, $expires);
        $headers = $this->getResponse()->getHeaders();
        $headers->addHeader($cookie);

        //$this->getSessionContainer()->ref = (string) $token;

        return $this->redirect()->toUrl($url);
    }

    /**
     * Set the response to a 404 error
     */
    protected function raise404()
    {
        $e = $this->getEvent();
        $e->setError(Application::ERROR_CONTROLLER_CANNOT_DISPATCH);
        $response = $e->getResponse();
        if ($response instanceof HttpResponse) {
            $response->setStatusCode(404);
        }
    }

    /**
     * Return an existing client or create a new one
     * @return HttpClient
     */
    public function getHttpClient()
    {
        if (!$this->httpClient) {
            $this->httpClient = new HttpClient(null, array(
                'adapter' => 'Zend\Http\Client\Adapter\Curl'
            ));
            $this->httpClient->setMethod('POST');
        }

        return $this->httpClient;
    }

    /**
     * Override HttpClient used for authenticating to the prismic api
     * @param  HttpClient $client
     * @return self
     */
    public function setHttpClient(HttpClient $client)
    {
        $this->httpClient = $client;

        return $this;
    }

    /**
     * Return session container for storing the access token
     * @return Container
     */
    public function getSessionContainer()
    {
        if (!$this->session) {
            $services = $this->getServiceLocator();
            $this->session = $services->get('NetgluePrismic\Session\PrismicContainer');
        }

        return $this->session;
    }

    /**
     * Return the client id as configured
     * @return string|null
     */
    protected function getClientId()
    {
        $sl = $this->getServiceLocator();
        $config = $sl->get('config');
        if (isset($config['prismic']['clientId'])) {
            return $config['prismic']['clientId'];
        }

        return null;
    }

    /**
     * Return the client secret as configured
     * @return string|null
     */
    protected function getClientSecret()
    {
        $sl = $this->getServiceLocator();
        $config = $sl->get('config');
        if (isset($config['prismic']['clientSecret'])) {
            return $config['prismic']['clientSecret'];
        }

        return null;
    }

    /**
     * Get a reference to the Prismic context
     * @return \NetgluePrismic\Context
     */
    public function getContext()
    {
        return $this->getServiceLocator()->get('Prismic\Context');
    }

    /**
     * Controller action to allow the user to change the current ref
     * @return void
     */
    public function changeRefAction()
    {
        $ref = $this->params()->fromQuery('ref');
        $url = $this->params()->fromQuery('url');
        if (!empty($url)) {
            try {
                $uri = new \Zend\Uri\Uri($url);
                $redirect = (string) $uri;
            } catch (\Exception $e) {
                // Not caught because $uri is tested below:
            }
        }
        if (!isset($redirect) || !isset($ref)) {
            $this->raise404();
            return;
        }

        // Make sure ref is valid
        $ref = $this->getContext()->getRefWithString($ref);
        if (!is_object($ref)) {
            $this->raise404();
            return;
        }

        // Store the ref in the session
        $this->getSessionContainer()->setRef($ref);

        // Redirect to the determined url
        return $this->redirect()->toUrl($redirect);
    }

    /**
     * Set the expected webhook secret
     *
     * @param  string $secret
     * @return self
     */
    public function setWebhookSecret($secret)
    {
        $this->webhookSecret = $secret;

        return $this;
    }

    /**
     * Respond to a posted webhook trigger
     * @return void
     */
    public function webhookAction()
    {
        if (!$this->getRequest()->isPost()) {
            return $this->getResponse()
                ->setStatusCode(400)
                ->setContent('Expected a POST Request' . PHP_EOL);
        }

        $json = $this->getRequest()->getContent();

        if(empty($json)) {
            return $this->getResponse()
                ->setStatusCode(500)
                ->setContent('Invalid JSON Data' . PHP_EOL);
        }

        try {
            $params = \Zend\Json\Json::decode($json);
        } catch (\Zend\Json\Exception\ExceptionInterface $e) {
            return $this->getResponse()
                ->setStatusCode(500)
                ->setContent('Invalid JSON Data' . PHP_EOL);
        }

        // The JSON Payload should include the plain text secret as stdClass->secret
        if ((string) $params->secret !== (string) $this->webhookSecret) {
            return $this->getResponse()
                ->setStatusCode(401)
                ->setContent('Unauthorised' . PHP_EOL);
        }

        $this->getEventManager()->trigger(__FUNCTION__, $this, (array) $params);

        return $this->getResponse()
            ->setStatusCode(200)
            ->setContent(PHP_EOL);
    }

}
