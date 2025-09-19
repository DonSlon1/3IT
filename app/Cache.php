<?php

declare(strict_types=1);

namespace app;

/**
 * Simple file-based cache implementation using JSON serialization
 *
 * Provides basic caching functionality with TTL support for improved performance.
 * Uses JSON instead of PHP serialization for security reasons.
 */
class Cache
{
    private static string $cacheDir = __DIR__ . '/../zeta/cache/';

    /**
     * Retrieve a value from cache
     *
     * @param string $key Cache key
     * @return mixed|null Cached value or null if not found/expired
     */
    public static function get(string $key): mixed
    {
        $file = self::$cacheDir . md5($key) . '.cache';

        if (!file_exists($file)) {
            return null;
        }

        $content = file_get_contents($file);
        if ($content === false) {
            return null;
        }

        $data = json_decode($content, true);
        if (!is_array($data) || !isset($data['expires'], $data['value'])) {
            // Invalid cache file, clean it up
            unlink($file);
            return null;
        }

        if ($data['expires'] < time()) {
            unlink($file);
            return null;
        }

        return $data['value'];
    }

    /**
     * Store a value in cache with TTL
     *
     * @param string $key Cache key
     * @param mixed $value Value to cache
     * @param int $ttl Time to live in seconds (default: 1 hour)
     * @return void
     */
    public static function set(string $key, mixed $value, int $ttl = 3600): void
    {
        if (!is_dir(self::$cacheDir)) {
            mkdir(self::$cacheDir, 0755, true);
        }

        $data = [
            'value' => $value,
            'expires' => time() + $ttl
        ];

        $jsonData = json_encode($data, JSON_THROW_ON_ERROR);
        file_put_contents(
            self::$cacheDir . md5($key) . '.cache',
            $jsonData,
            LOCK_EX
        );
    }

    /**
     * Delete a specific cache entry
     *
     * @param string $key Cache key to delete
     * @return void
     */
    public static function delete(string $key): void
    {
        $file = self::$cacheDir . md5($key) . '.cache';
        if (file_exists($file)) {
            unlink($file);
        }
    }

    /**
     * Clear all cache entries
     *
     * @return void
     */
    public static function clear(): void
    {
        if (is_dir(self::$cacheDir)) {
            $files = glob(self::$cacheDir . '*.cache');
            if ($files !== false) {
                foreach ($files as $file) {
                    unlink($file);
                }
            }
        }
    }
}