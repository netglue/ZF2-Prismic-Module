<?php

namespace NetgluePrismic\Cache;

use Prismic\Cache\CacheInterface;

use Zend\Cache\Storage\StorageInterface;
use Zend\Cache\Storage\FlushableInterface;

class Facade implements CacheInterface
{

    /**
     * Storage Interface
     * @var StorageInterface The cache instance we are proxying to
     */
    private $storage;

    /**
     * @param StorageInterface $storage
     * @return void
     */
    public function __construct(StorageInterface $storage)
    {
        $this->storage = $storage;
    }

    /**
     * Returns the value of a cache entry from its key
     *
     * @param  string    $key the key of the cache entry
     * @return mixed the value of the entry
     */
    public function get($key)
    {
        return $this->storage->getItem($key);
    }

    /**
     * Stores a new cache entry
     *
     * @param string    $key   the key of the cache entry
     * @param mixed     $value the value of the entry
     * @param integer   $ttl   the time until this cache entry expires
     * @return bool
     */
    public function set($key, $value, $ttl = 0)
    {
        /**
         * The ttl is ignored here as normally, with Zend cache you set the ttl
         * for the cache instance in options.
         * The side effect of this, is that api data will likely be cached for longer
         * than 5 seconds (The value used in the prismic sdk), but providing you have
         * the shipped cache buster working, the cache will be flushed on api update
         */
        return $this->storage->setItem($key, $value);
    }

    /**
     * Deletes a cache entry, from its key
     *
     * @param string $key the key of the cache entry
     * @return bool
     */
    public function delete($key)
    {
        return $this->storage->removeItem($key);
    }

    /**
     * Clears the whole cache
     * @return bool
     */
    public function clear()
    {
        if($this->storage instanceof FlushableInterface) {
            return $this->storage->flush();
        }

        /**
         * All Zend Storage instances implement FlushableInterface
         */
        // @codeCoverageIgnoreStart
        return false;
        // @codeCoverageIgnoreEnd
    }

    /**
     * Return underlying storage instance
     * @return StorageInterface
     */
    public function getStorage()
    {
        return $this->storage;
    }

}
