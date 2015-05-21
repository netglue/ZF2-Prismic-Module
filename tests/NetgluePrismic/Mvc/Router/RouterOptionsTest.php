<?php

namespace NetgluePrismic\Mvc\Router;

class RouterOptionsTest extends \Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase
{
    public function setUp()
    {
        $this->setTraceError(false);
        $this->setApplicationConfig(include __DIR__ . '/../../../TestConfig.php.dist');
        parent::setUp();

    }

    public function testBasic()
    {
        $options = new RouterOptions(array(
            'bookmark' => 'bookmark',
            'mask'     => 'mask',
            'ref'      => 'ref',
            'id'       => 'prismic-id',
            'slug'     => 'slug',
        ));

        $this->assertSame('bookmark', $options->getBookmarkParam());
        $this->assertSame('mask', $options->getMaskParam());
        $this->assertSame('ref', $options->getRefParam());
        $this->assertSame('prismic-id', $options->getIdParam());
        $this->assertSame('slug', $options->getSlugParam());

    }
}
