<?php

namespace NetgluePrismic\Mvc\Router;

class RouterOptions
{

    protected $bookmark;
    protected $mask;
    protected $ref;
    protected $id;
    protected $slug;

    public function __construct(array $options)
    {
        $valid = array(
            'bookmark',
            'mask',
            'ref',
            'id',
            'slug'
        );
        foreach($valid as $name) {
            $this->{$name} = isset($options[$name]) ? (string) $options[$name] : $name;
        }
    }

    public function getParam($name)
    {
        return isset($this->{$name}) ? $this->{$name} : NULL;
    }

    public function getIdParam()
    {
        return $this->getParam('id');
    }

    public function getBookmarkParam()
    {
        return $this->getParam('bookmark');
    }

    public function getMaskParam()
    {
        return $this->getParam('mask');
    }

    public function getRefParam()
    {
        return $this->getParam('ref');
    }

    public function getSlugParam()
    {
        return $this->getParam('slug');
    }

}
