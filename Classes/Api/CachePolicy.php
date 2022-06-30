<?php

namespace Oracle\Typo3Dam\Api;

/**
 * Represents the Cache Policy of the request.
 */
class CachePolicy
{
    public const OFF = 0;
    public const EVERYTHING = 1;
    public const TOKEN_ONLY = 2;

    /**
     * @var int
     */
    protected $cacheType;

    /**
     * @var int
     */
    protected $lifetime;

    /**
     * Creates a new CachePolicy.
     *
     * @param int $cacheType The behaviour of the cache. Must be one of the CachePolicy constants.
     * @param int $lifetime  The cache lifetime in seconds.
     */
    public function __construct(int $cacheType, int $lifetime)
    {
        $this->setCacheType($cacheType);
        $this->lifetime = $lifetime;
    }

    protected function setCacheType($cacheType)
    {
        if (!in_array($cacheType, [self::OFF, self::EVERYTHING, self::TOKEN_ONLY])) {
            throw new \OutOfRangeException(
                'Invalid cache type "' . $cacheType . '". Must be one of the CachePolicy constants.'
            );
        }
        $this->cacheType = $cacheType;
    }

    public function isEnabled()
    {
        return self::OFF != $this->cacheType;
    }

    public function getCacheType()
    {
        return $this->cacheType;
    }

    public function getLifetime()
    {
        return $this->lifetime;
    }
}
