<?php

namespace NetgluePrismic\Mvc\Listener;

class CacheBusterListenerTest extends \Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase
{

    public function setUp()
    {
        $this->setApplicationConfig(include __DIR__ . '/../../../TestConfig.php.dist');
        parent::setUp();
    }

    /**
     * @expectedException NetgluePrismic\Exception\RuntimeException
     * @expectedExceptionMessage Prismic API has not been injected
     */
    public function testClearCacheThrowsExceptionWithoutApiAvailable()
    {
        $listener = new CacheBusterListener;
        $listener->clearCache();
    }

    public function testCacheIsClearedOnWebhookDispatchSuccess()
    {
        $payload = <<<JSON
{
  "type" : "api-update",
  "masterRef" : "VDfCKCkAACcAuzcg",
  "releases" : {
    "update" : [ {
      "id" : "VDVclTQAADIALvdb",
      "ref" : "VDfCKSkAACgAuzck",
      "label" : "A Far Future Release"
    }, {
      "id" : "VDUTRysAACgAfsrg",
      "ref" : "VDfCKSkAACwAuzcl",
      "label" : "Unit Test Release Not To Be Published"
    } ]
  },
  "bookmarks" : { },
  "masks" : { },
  "collection" : { },
  "tags" : { },
  "experiments" : { },
  "domain" : "zf2-module",
  "apiUrl" : "https://zf2-module.prismic.io/api",
  "secret" : "VerySerious"
}
JSON;

        $listener = new CacheBusterListener;
        $this->getApplicationServiceLocator()->get('EventManager')->attach($listener);
        $this->assertNull($listener->lastPayload);

        $this->getRequest()->setContent($payload);
        $this->dispatch('/prismic-webhook', 'POST');
        $this->assertResponseStatusCode(200);

        $this->assertInternalType('array', $listener->lastPayload);
        $this->assertEquals('api-update', $listener->lastPayload['type']);

    }

}
