<?php

namespace NetgluePrismic\Cache;

use Prismic\Cache\CacheInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\CacheException;

class PrismicPsrCacheFacade implements CacheInterface
{

    /**
     * @var CacheItemPoolInterface The cache instance we are proxying to
     */
    private $storage;

    public function __construct(CacheItemPoolInterface $storage)
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
        $item = $this->storage->getItem($normalizedKey);
        return $item->get();
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
        $normalizedKey = $this->normalizeKey($key);
        $item = $this->storage->getItem($normalizedKey);
        $item->set($value);
        $this->storage->save($item);
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
        $this->storage->deleteItem($normalizedKey);
    }

    /**
     * Clears the whole cache
     * @return void
     */
    public function clear()
    {
        $this->storage->clear();
    }

    /**
     * Return underlying storage instance
     */
    public function getStorage() : CacheItemPoolInterface
    {
        return $this->storage;
    }

    /**
     * In certain situations, the cache key is modified to prevent un-needed exceptions/errors
     *
     * If the storage adapter reports a max key length smaller than the length of the resulting key,
     * the key returned is an md5 hash of the key
     *
     * The method is public so that client code can discover what the normalized key might be
     * if there was a need.
     *
     */
    public function normalizeKey(string $key) : string
    {
        try {
            $item = $this->storage->getItem($key);
        } catch (CacheException $e) {
            $key = md5($key);
        }
        return $key;
    }
}
