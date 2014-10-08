<?php

namespace NetgluePrismic\Mvc\Controller;
use NetgluePrismic\bootstrap;

class PrismicControllerTest extends \Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase
{

    protected $context;

    public function setUp()
    {
        $this->setTraceError(false);
        $this->setApplicationConfig(include __DIR__ . '/../../../TestConfig.php.dist');
        parent::setUp();
        $services = $this->getApplicationServiceLocator();
        $this->context = $services->get('Prismic\Context');

    }

    public function testSigninActionThrowsExceptionWithEmptyCredentials()
    {
        $this->dispatch('/prismic-signin');
        $this->assertResponseStatusCode(500);
        // Throws NetgluePrismic\Exception\InvalidCredentialsException
    }

    public function setInvalidCredentials()
    {
        $services = $this->getApplicationServiceLocator();
        $config = $services->get('config');
        $config['prismic']['clientId'] = 'ClientID';
        $config['prismic']['clientSecret'] = 'ClientSecret';
        $services->setAllowOverride(true);
        $services->setService('config', $config);
    }


    public function testSigninActionRedirectsWithCredentials()
    {
        $this->setInvalidCredentials();
        $this->dispatch('/prismic-signin');
        $this->assertRedirect();
    }

    public function testCallbackThrows404WithoutCodeParamInGet()
    {
        $this->dispatch('/prismic-signin/callback');
        $this->assertResponseStatusCode(404);
    }

    public function testCallbackThrows404WithInvalidCredentials()
    {
        $this->setTraceError(true);
        $this->setInvalidCredentials();
        $this->dispatch('/prismic-signin/callback', 'GET', array('code' => 1234));
        $this->assertResponseStatusCode(404);
    }

    public function testSuccessfullAuthSetsSessionToken()
    {
        $json = '{"access_token":"ACCESS_TOKEN","expires_in":3600}';
        $mockResponse = $this->getMock('\Zend\Http\Response');
        $mockResponse->expects($this->any())
            ->method('getBody')
            ->will($this->returnValue($json));
        $mockResponse->expects($this->any())
            ->method('isSuccess')
            ->will($this->returnValue(true));

        $mockClient = $this->getMock('\Zend\Http\Client');
        $mockClient->expects($this->any())
            ->method('send')
            ->will($this->returnValue($mockResponse));

        $this->setInvalidCredentials();
        $services = $this->getApplicationServiceLocator();
        $manager = $services->get('ControllerManager');
        $controller = $manager->get('NetgluePrismic\Mvc\Controller\PrismicController');


        $controller->setHttpClient($mockClient);
        $this->assertSame($mockClient, $controller->getHttpClient());

        $this->dispatch('/prismic-signin/callback', 'GET', array('code' => 1234));
        $this->assertRedirect();

        $session = $controller->getSessionContainer();
        $this->assertEquals('ACCESS_TOKEN', $session->access_token);
    }

    public function testChangeRefActionReturns404ForInvalidRef()
    {
        $newRef = 'NewRepoRef';
        $get = array(
            'ref' => $newRef,
            'url' => 'http://localhost/foo',
        );
        $this->dispatch('/change-repository-ref', 'GET', $get);
        $this->assertResponseStatusCode(404);
    }

    public function testChangeRefActionSetsRefInSessionAndContext()
    {
        $allRefs = $this->context->getPrismicApi()->refs();
        $notMaster = array_filter($allRefs, function($value) {
            return !($value->isMasterRef());
        });
        if(!count($notMaster)) {
            $this->fail('There are no unpublised releases available in the prismic repository');
        }
        $notMaster = current($notMaster)->getRef();

        // Make sure session and context both use the master ref
        $this->assertNotSame($notMaster, (string) $this->context->getRef());
        $session = $this->getApplicationServiceLocator()->get('NetgluePrismic\Session\PrismicContainer');
        $this->assertNotSame($notMaster, $session->ref);

        $get = array(
            'ref' => $notMaster,
            'url' => 'http://localhost/foo',
        );
        $this->dispatch('/change-repository-ref', 'GET', $get);
        $this->assertRedirectTo('http://localhost/foo');
        $this->assertSame($notMaster, $session->ref);
    }

}
