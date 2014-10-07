<?php

namespace NetgluePrismic\Mvc;

class RouterOptionsTest extends \PHPUnit_Framework_TestCase
{
    public function testBasic()
    {
        $this->assertTrue(true);
        $options = new RouterOptions(array(
            'bookmark' => 'foo',
        ));

        $this->assertSame('foo', $options->getBookmarkParam());

    }
}
