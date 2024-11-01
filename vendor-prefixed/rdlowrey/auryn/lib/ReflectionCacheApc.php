<?php

namespace Whisk\Recipes\Vendor\Auryn;

class ReflectionCacheApc implements \Whisk\Recipes\Vendor\Auryn\ReflectionCache
{
    private $localCache;
    private $timeToLive = 5;
    public function __construct(\Whisk\Recipes\Vendor\Auryn\ReflectionCache $localCache = null)
    {
        $this->localCache = $localCache ?: new \Whisk\Recipes\Vendor\Auryn\ReflectionCacheArray();
    }
    public function setTimeToLive($seconds)
    {
        $seconds = (int) $seconds;
        $this->timeToLive = $seconds > 0 ? $seconds : $this->timeToLive;
        return $this;
    }
    public function fetch($key)
    {
        $localData = $this->localCache->fetch($key);
        if ($localData != \false) {
            return $localData;
        } else {
            $success = null;
            // stupid by-ref parameter that scrutinizer complains about
            $data = \apc_fetch($key, $success);
            return $success ? $data : \false;
        }
    }
    public function store($key, $data)
    {
        $this->localCache->store($key, $data);
        \apc_store($key, $data, $this->timeToLive);
    }
}
