<?php

namespace NetgluePrismic\Service;

use NetgluePrismic\bootstrap;
use Zend\Cache\StorageFactory;

class SitemapGeneratorTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @expectedException NetgluePrismic\Exception\InvalidArgumentException
     * @expectedExceptionMessage Encountered property map key that was not a string
     */
    public function testSetPropertyMapThrowsExceptionForNonStringPropertyName()
    {
        $generator = new SitemapGenerator;
        $generator->setPropertyMap(array(
            1 => 'foo',
        ));
    }

    /**
     * @expectedException NetgluePrismic\Exception\InvalidArgumentException
     * @expectedExceptionMessage Encountered fragment identifier that was not a string
     */
    public function testSetPropertyMapThrowsExceptionForNonStringFragmentName()
    {
        $generator = new SitemapGenerator;
        $generator->setPropertyMap(array(
            'foo' => 1,
        ));
    }

}
