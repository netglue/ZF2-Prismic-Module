<?php

namespace NetgluePrismic\View\Helper;

use NetgluePrismic\bootstrap;

class FinderTest extends \PHPUnit_Framework_TestCase
{

    public function testGetViewHelper()
    {
        $services = bootstrap::getServiceManager();
        $manager = $services->get('ViewHelperManager');
        $helper = $manager->create('NetgluePrismic\View\Helper\Finder');
        $this->assertInstanceOf('NetgluePrismic\View\Helper\Finder', $helper);
        $this->assertInstanceOf('NetgluePrismic\Context', $helper->getContext());

        return $helper;
    }

    /**
     * @depends testGetViewHelper
     */
    public function testGetDocumentById(Finder $helper)
    {
        $id = 'VDRgLysAACoAfWTE';
        $this->assertInstanceOf('Prismic\Document', $helper->getDocumentById($id));
        $this->assertNull($helper->getDocumentById('Unknown'));
    }

    /**
     * @depends testGetViewHelper
     */
    public function testInvokeReturnsSelf(Finder $helper)
    {
        $this->assertSame($helper, $helper());
    }

}
