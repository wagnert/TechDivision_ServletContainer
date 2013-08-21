<?php

namespace TechDivision\ServletContainer\Session\Storage;

use TechDivision\ServletContainer\Session\Storage\StorageInterface;

class InitialContextStorage implements StorageInterface {
    
    /**
     * The initial context instance.
     * @var \TechDivision\ApplicationServer\InitialContext
     */
    protected $backend;

    /**
     * The unique cache identifier.
     * @var string
     */
    protected $identifier = '';
    
    /**
     * Injects the storage with the inital context instance.
     * 
     * @param \TechDivision\ApplicationServer\InitialContext $initialContext The initial context instance
     * @return void
     */
    public function injectBackend($backend) {
        $this->backend = $backend;
    }

    /**
     * Initializes the context with the connection to the persistence
     * backend, e. g. Memcached
     *
     * @return void
     */
    public function __construct($identifier = '') {
        $this->identifier = $identifier;
    }

    /**
     * Returns this cache's identifier
     *
     * @return string The identifier for this cache
     * @api
     */
    public function getIdentifier() {
        return $this->identifier;
    }

    /**
     * Returns the backend used by this cache
     *
     * @return \TechDivision\ApplicationServer\InitialContext The backend used by this cache
     */
    public function getBackend() {
        return $this->backend;
    }

    /**
     * Saves data in the cache.
     *
     * @param string $entryIdentifier Something which identifies the data - depends on concrete cache
     * @param mixed $data The data to cache - also depends on the concrete cache implementation
     * @param array $tags Tags to associate with this cache entry
     * @param integer $lifetime Lifetime of this cache entry in seconds. If NULL is specified, the default lifetime is used. "0" means unlimited lifetime.
     * @return void
     * @api
     */
    public function set($entryIdentifier, $data, array $tags = array(), $lifetime = NULL) {

        $cacheKey = $this->getIdentifier() . $entryIdentifier;

        $this->backend->setAttribute($cacheKey, $data, $lifetime);

        foreach ($tags as $tag) {

            $tagData = $this->backend->getAttribute($this->getIdentifier() . $tag);

            if (is_array($tagData) && in_array($cacheKey, $tagData, true) === true) {
                // do nothing here
            } elseif (is_array($tagData) && in_array($cacheKey, $tagData, true) === false) {
                $tagData[] = $cacheKey;
            } else {
                $tagData = array($cacheKey);
            }

            $this->backend->setAttribute($tag, $tagData);
        }
    }

    /**
     * Finds and returns data from the cache.
     *
     * @param string $entryIdentifier Something which identifies the cache entry - depends on concrete cache
     * @return mixed
     * @api
     */
    public function get($entryIdentifier) {
        return $this->backend->getAttribute($this->getIdentifier() . $entryIdentifier);
    }

    /**
     * Finds and returns all cache entries which are tagged by the specified tag.
     *
     * @param string $tag The tag to search for
     * @return array An array with the identifier (key) and content (value) of all matching entries. An empty array if no entries matched
     * @api
     */
    public function getByTag($tag) {
        return $this->backend->getAttribute($this->getIdentifier() . $tag);
    }

    /**
     * Checks if a cache entry with the specified identifier exists.
     *
     * @param string $entryIdentifier An identifier specifying the cache entry
     * @return boolean TRUE if such an entry exists, FALSE if not
     * @api
     */
    public function has($entryIdentifier) {
        if ($this->get($this->getIdentifier() . $entryIdentifier) !== false) {
            return true;
        }
        return false;
    }

    /**
     * Removes the given cache entry from the cache.
     *
     * @param string $entryIdentifier An identifier specifying the cache entry
     * @return boolean TRUE if such an entry exists, FALSE if not
     */
    public function remove($entryIdentifier) {
        $this->backend->removeAttribute($this->getIdentifier() . $entryIdentifier);
    }

    /**
     * Removes all cache entries of this cache.
     *
     * @return void
     */
    public function flush() {

        $allKeys = $this->backend->getAllKeys();

        foreach ($allKeys as $key) {
            if (substr_compare($key, $this->getIdentifier(), 0)) {
                $this->remove($key);
            }
        }
    }

    /**
     * Removes all cache entries of this cache which are tagged by the specified tag.
     *
     * @param string $tag The tag the entries must have
     * @return void
     * @api
     */
    public function flushByTag($tag) {

        $tagData = $this->backend->getAttribute($this->getIdentifier() . $tag);

        if (is_array($tagData)) {

            foreach ($tagData as $cacheKey) {
                $this->remove($cacheKey);
            }

            $this->remove($this->getIdentifier() . $tag);
        }
    }

    /**
     * Does garbage collection
     *
     * @return void
     * @api
     */
    public function collectGarbage() {
        // nothing to do here, because gc is handled by memcache
    }

    /**
     * Checks the validity of an entry identifier. Returns true if it's valid.
     *
     * @param string $identifier An identifier to be checked for validity
     * @return boolean
     * @api
     */
    public function isValidEntryIdentifier($identifier) {
        if (preg_match('^[0-9A-Za-z_]+$', $identifier) === 1) {
            return true;
        }
        return false;
    }

    /**
     * Checks the validity of a tag. Returns true if it's valid.
     *
     * @param string $tag A tag to be checked for validity
     * @return boolean
     * @api
     */
    public function isValidTag($tag) {
        return $this->isValidEntryIdentifier($tag);
    }
}