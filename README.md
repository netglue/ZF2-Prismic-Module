# ZF2 Module for quick and easy installation of Prismic PHP SDK

## Routing

The module can currently helpfully determine which document corresponds to which Controller/Action by adding additional parameters to your routes.

Displaying a specific document that has been bookmarked would go something like this:
    
    // ...
    'myRouteName' => array(
        'type' => 'Literal',
        'options' => array(
            'route' => '/some-where',
            'defaults' => array(
                'controller' => 'My\Controller',
                'action' => 'my-place',
                'bookmark' => 'my-bookmark-name',
            ),
        ),
    ),
    // ...

Then, in your `My\Controller` :
    
    public function myPlaceAction()
    {
        $document = $this->prismic()->getDocument();
    }
    

    'prismic' => array(
        'routeParameters' => array(
            'bookmark' => 'bookmark',
            'mask'     => 'mask',
            'ref'      => 'ref',
            'id'       => 'prismic-id',
            'slug'     => 'slug',
        ),
    ),


## Automatic Page Meta

If you are in the habit of designing document masks that use the same document field for common page elements such as `<title>` and meta description, you can configure the module to watch out for these and trigger the appropriate view helper automatically. Current supported elements are:
    
    <title>
    <meta name="description">
    <meta property="og:title">
    <meta property="og:description">
    <meta property="og:image">

The automation is implemented by way of a listener that listens to the Prismic controller plugin `NetgluePrismic\Mvc\Controller\Plugin\Prismic::setDocument` method. Once we know what document we're dealing with for the current request, if enabled, the listener will examine the document and try to pair up document fields with meta data and call the appropriate zend view helper with the found information. Example configuration is found in `config/module.config.php`

By default the listener, although attached, is not enabled.

