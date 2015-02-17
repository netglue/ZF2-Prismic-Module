<?php

namespace NetgluePrismic;

use Prismic\Ref;

class ContextTest extends \PHPUnit_Framework_TestCase
{

    protected $context;

    public function setup()
    {
        $cache = new \Prismic\Cache\ApcCache();
        $cache->clear();
        $sl = bootstrap::getServiceManager();
        $this->context = $sl->get('NetgluePrismic\Context');

    }

    public function testGetContext()
    {
        $this->assertInstanceOf('NetgluePrismic\Context', $this->context);

        return $this->context;
    }

    public function testGetRefReturnsMasterRefByDefault()
    {
        $ref = $this->context->getRef();
        $this->assertInstanceOf('Prismic\Ref', $ref);
        $this->assertTrue($ref->isMasterRef());
    }

    public function testSetGetRefBasic()
    {
        $ref = new Ref('test', 'test', 'test', false);
        $context = new Context;
        $context->setRef($ref);
        $this->assertSame($ref, $context->getRef());
    }

    public function testGetRefAsStringReturnsMasterRefByDefault()
    {
        $master = $this->context->getMasterRef();
        $this->assertSame($master->getRef(), $this->context->getRefAsString());
    }

    public function testSettingStringRefOverridesRefObjectWhenSet()
    {
        $master = $this->context->getMasterRef();
        $refObject = $this->context->getRef();
        $this->assertSame($master, $refObject);

        $this->context->setRefWithString('foo');
        $this->assertSame('foo', $this->context->getRefAsString());
    }

    public function testSettingStringRefCanBeNull()
    {
        $master = $this->context->getMasterRef();
        $this->context->setRefWithString(null);
        $this->assertSame($master->getRef(), $this->context->getRefAsString());
    }

    public function testSetGetPrivilegedAccess()
    {
        $context = new Context;
        $this->assertFalse($context->getPrivilegedAccess());
        $context->setPrivilegedAccess(true);
        $this->assertTrue($context->getPrivilegedAccess());
    }

    public function testToString()
    {
        $ref = $this->context->getRef();
        $id = $ref->getRef();

        $string = (string) $this->context;
        $this->assertSame($id, $string);
    }

    public function testGetDocumentById()
    {
        $id = 'VDRgLysAACoAfWTE';
        $doc = $this->context->getDocumentById($id);
        $this->assertInstanceOf('Prismic\Document', $doc);

        $doc = $this->context->getDocumentById('Not An Id');
        $this->assertNull($doc);
    }

    public function testGetDocumentByBookmark()
    {
        $bookmark = 'unit-test-bookmark';
        $expectId = 'VDRgLysAACoAfWTE';
        $doc = $this->context->getDocumentByBookmark($bookmark);
        $this->assertInstanceOf('Prismic\Document', $doc);
        $this->assertEquals($expectId, $doc->getId());
    }

    /**
     * @expectedException NetgluePrismic\Exception\RuntimeException
     * @expectedExceptionMessage bookmark does not exist in this repository
     */
    public function testGetDocByBookmarkThrowsExceptionForInvalidBookmark()
    {
        $this->assertNull($this->context->getDocumentByBookmark('Not a bookmark'));
    }

    /**
     * @expectedException NetgluePrismic\Exception\DocumentNotFoundException
     * @expectedExceptionMessage Expected a document instance or a valid document id
     */
    public function testFindBookmarkByDocumentThrowsExceptionForNonDocOrId()
    {
        $this->context->findBookmarkByDocument('Not an id');
    }

    public function testFindBookmarkByDocument()
    {
        $notBookmarked = 'VDRjFSsAACoAfWqX';
        $bookmarked = 'VDRgLysAACoAfWTE';
        $expect = 'unit-test-bookmark';

        $this->assertEquals($expect, $this->context->findBookmarkByDocument($bookmarked));
        $this->assertInstanceOf('Prismic\Document', $this->context->getDocumentById($notBookmarked));
        $this->assertNull($this->context->findBookmarkByDocument($notBookmarked));

        $this->assertTrue($this->context->isBookmarked($bookmarked));
        $this->assertFalse($this->context->isBookmarked($notBookmarked));
    }

    public function testGetRefWithStringBasic()
    {
        $master = $this->context->getMasterRef();

        $this->assertSame($master, $this->context->getRefWithString( $master->getRef() ));
    }
}
