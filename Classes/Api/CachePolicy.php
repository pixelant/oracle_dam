<?php

declare(strict_types=1);

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

    /**
     * @param $cacheType
     * @return void
     */
    protected function setCacheType(int $cacheType): void
    {
        if (!in_array($cacheType, [self::OFF, self::EVERYTHING, self::TOKEN_ONLY])) {
            throw new \OutOfRangeException(
                'Invalid cache type "' . $cacheType . '". Must be one of the CachePolicy constants.'
            );
        }
        $this->cacheType = $cacheType;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return self::OFF !== $this->cacheType;
    }

    /**
     * @return int
     */
    public function getCacheType(): int
    {
        return $this->cacheType;
    }

    /**
     * @return int
     */
    public function getLifetime(): int
    {
        return $this->lifetime;
    }
}
