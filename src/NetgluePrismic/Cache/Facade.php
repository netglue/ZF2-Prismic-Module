<?php

namespace NetgluePrismic\Cache;

use Prismic\Cache\CacheInterface;
use Zend\Cache\Storage\StorageInterface;
use Zend\Cache\Storage\FlushableInterface;
use Zend\Cache\Storage\Adapter\Memcached;

class Facade implements CacheInterface
{

    /**
     * Storage Interface
     * @var StorageInterface The cache instance we are proxying to
     */
    private $storage;

    /**
     * @param  StorageInterface $storage
     */
    public function __construct(StorageInterface $storage)
    {
        $this->storage = $storage;
    }

    /**
     * Whether the given key exists in the cache
     * @param  string $key
     * @return bool
     */
    public function has($key)
    {
        $normalizedKey = $this->normalizeKey($key);

        return $this->storage->hasItem($normalizedKey);
    }

    /**
     * Returns the value of a cache entry from its key
     *
     * @param  string $key the key of the cache entry
     * @return mixed  the value of the entry
     */
    public function get($key)
    {
        $normalizedKey = $this->normalizeKey($key);

        return $this->storage->getItem($normalizedKey);
    }

    /**
     * Stores a new cache entry
     *
     * @param  string  $key   the key of the cache entry
     * @param  mixed   $value the value of the entry
     * @param  integer $ttl   the time until this cache entry expires
     * @return void
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
        $normalizedKey = $this->normalizeKey($key);
        $this->storage->setItem($normalizedKey, $value);
    }

    /**
     * Deletes a cache entry, from its key
     *
     * @param  string $key the key of the cache entry
     * @return void
     */
    public function delete($key)
    {
        $normalizedKey = $this->normalizeKey($key);
        $this->storage->removeItem($normalizedKey);
    }

    /**
     * Clears the whole cache
     * @return void
     */
    public function clear()
    {
        if ($this->storage instanceof FlushableInterface) {
            $this->storage->flush();
        }
    }

    /**
     * Return underlying storage instance
     * @return StorageInterface
     */
    public function getStorage()
    {
        return $this->storage;
    }

    /**
     * In certain situations, the cache key is modified to prevent un-needed exceptions/errors
     *
     * This method boils down to Memcached not accepting keys with a length greater than 250
     * characters. If the storage mechanism is Memcached, then the key is returned hashed.
     *
     * The method is public so that client code can discover what the normalized key might be
     * if there was a need.
     *
     * @param  string $key The unmodified cache key to use
     * @return string the possibly modified cache key
     */
    public function normalizeKey($key)
    {
        if ($this->storage instanceof Memcached && strlen($key) > 250) {
            return md5($key);
        }

        return $key;
    }

}
