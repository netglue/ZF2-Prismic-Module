<?php

namespace NetgluePrismic\Mvc;
use Prismic\Fragment\Link\DocumentLink;
use Prismic\Fragment\Link\WebLink;

class LinkResolverTest extends \Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase
{
    private $resolver;
    private $context;

    public function setUp()
    {
        $this->setApplicationConfig(include __DIR__ . '/../../TestConfig.php.dist');
        parent::setUp();
        $services = $this->getApplicationServiceLocator();
        $this->resolver = $services->get('NetgluePrismic\Mvc\LinkResolver');
        $this->context = $services->get('Prismic\Context');
    }

    public function testGetRouteNameFromBookmark()
    {
        $this->assertEquals('test-bookmark', $this->resolver->getRouteNameFromBookmark('unit-test-bookmark'));
    }

    /**
     * @expectedException NetgluePrismic\Exception\RuntimeException
     * @expectedExceptionMessage No route could be found for the bookmark
     */
    public function testGetRouteNameFromBookmarkThrowsExceptionForUnknownBookmark()
    {
        $this->resolver->getRouteNameFromBookmark('unknown-bookmark');
    }

    public function testGetRouteNameFromMask()
    {
        $this->assertEquals('test-mask', $this->resolver->getRouteNameFromMask('test'));
    }

    /**
     * @expectedException NetgluePrismic\Exception\RuntimeException
     * @expectedExceptionMessage No route could be found for the specified document mask
     */
    public function testGetRouteNameFromMaskThrowsExceptionForUnknownBookmark()
    {
        $this->resolver->getRouteNameFromMask('unknown-mask');
    }

    public function testResolveWithBookmark()
    {
        $expect = '/test-bookmark';
        $doc = $this->context->getDocumentByBookmark('unit-test-bookmark');

        $id = $doc->getId();
        $type = $doc->getType();
        $tags = $doc->getTags();
        $slug = $doc->getSlug();
        $isBroken = false;
        $link = new DocumentLink($id, $type, $tags, $slug, $isBroken);

        $url = $this->resolver->resolve($link);
        $this->assertEquals($expect, $url);

    }

    public function testResolveWithMask()
    {
        $expect = '/masked/not/VDRjFSsAACoAfWqX';
        $doc = $this->context->getDocumentById('VDRjFSsAACoAfWqX');
        // Mask == test

        $id = $doc->getId();
        $type = $doc->getType();
        $tags = $doc->getTags();
        $slug = $doc->getSlug();
        $isBroken = false;
        $link = new DocumentLink($id, $type, $tags, $slug, $isBroken);

        $url = $this->resolver->resolve($link);
        $this->assertEquals($expect, $url);
    }

    /**
     * @expectedException NetgluePrismic\Exception\InvalidArgumentException
     * @expectedExceptionMessage Expected an instance of LinkInterface
     */
    public function testResolveThrowsExceptionForInvalidArgument()
    {
        $this->resolver->resolve('foo');
    }

    public function testResolveReturnsUrlForNonDocumentLinks()
    {
        $link = new WebLink('http://www.google.com');
        $result = $this->resolver->resolve($link);
        $this->assertEquals('http://www.google.com', $result);
    }

    public function testResolveReturnsNullForBrokenDocumentLinks()
    {
        $link = new DocumentLink('abcd', 'some-type', array('tag1'), 'snails', true);
        $result = $this->resolver->resolve($link);
        $this->assertNull($result);
    }

    public function testResolveReturnsNullForUnroutableDocumentLinks()
    {
        $link = new DocumentLink('abcd', 'some-type', array('tag1'), 'snails', false);
        $result = $this->resolver->resolve($link);
        $this->assertNull($result);
    }
}
