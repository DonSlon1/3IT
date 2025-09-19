<?php namespace app;

class Cache
{
    private static string $cacheDir = __DIR__ . '/../zeta/cache/';

    public static function get(string $key): mixed
    {
        $file = self::$cacheDir . md5($key) . '.cache';

        if (!file_exists($file)) {
            return null;
        }

        $data = unserialize(file_get_contents($file));

        if ($data['expires'] < time()) {
            unlink($file);
            return null;
        }

        return $data['value'];
    }

    public static function set(string $key, mixed $value, int $ttl = 3600): void
    {
        if (!is_dir(self::$cacheDir)) {
            mkdir(self::$cacheDir, 0755, true);
        }

        $data = [
            'value' => $value,
            'expires' => time() + $ttl
        ];

        file_put_contents(
            self::$cacheDir . md5($key) . '.cache',
            serialize($data)
        );
    }

    public static function delete(string $key): void
    {
        $file = self::$cacheDir . md5($key) . '.cache';
        if (file_exists($file)) {
            unlink($file);
        }
    }

    public static function clear(): void
    {
        if (is_dir(self::$cacheDir)) {
            $files = glob(self::$cacheDir . '*.cache');
            foreach ($files as $file) {
                unlink($file);
            }
        }
    }
}