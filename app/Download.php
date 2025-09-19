<?php

declare(strict_types=1);

namespace app;

use Exception;

/**
 * Data import controller for remote JSON data sources
 *
 * Handles secure downloading and importing of JSON data from remote sources
 * with caching, validation, and database transaction management.
 */
class Download implements App
{
    private const DATA_URL = "https://test.3it.cz/data/json";
    private const CACHE_TTL = 300; // 5 minutes
    private const REQUEST_TIMEOUT = 10;

    /**
     * Execute data import process
     *
     * Downloads JSON data from remote source, validates it, and imports
     * into database with proper error handling and caching.
     *
     * @return void
     */
    public function run(): void
    {
        try {
            $data = $this->fetchDataWithCache();
            $this->validateData($data);

            $stats = $this->importData($data);

            $this->redirectWithSuccess();

        } catch (Exception $e) {
            ErrorHandler::handleException($e, 'Failed to import data: ' . $e->getMessage());
        }
    }

    /**
     * Fetch data from remote source with caching
     *
     * @return array Raw JSON data from remote source
     * @throws Exception If data cannot be fetched or parsed
     */
    private function fetchDataWithCache(): array
    {
        $cacheKey = 'json_data_' . md5(self::DATA_URL);
        $data = Cache::get($cacheKey);

        if ($data === null) {
            $data = $this->downloadRemoteData();

            if ($data && is_array($data)) {
                Cache::set($cacheKey, $data, self::CACHE_TTL);
            }
        }

        return $data;
    }

    /**
     * Download data from remote JSON source
     *
     * @return array Parsed JSON data
     * @throws Exception If download fails or JSON is invalid
     */
    private function downloadRemoteData(): array
    {
        $context = stream_context_create([
            'http' => [
                'timeout' => self::REQUEST_TIMEOUT,
                'user_agent' => 'PHP CRM Application/1.0',
                'method' => 'GET'
            ]
        ]);

        $content = @file_get_contents(self::DATA_URL, false, $context);

        if ($content === false) {
            throw new Exception("Failed to download data from remote source");
        }

        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Invalid JSON format: " . json_last_error_msg());
        }

        return $data;
    }

    /**
     * Validate imported data structure
     *
     * @param mixed $data Data to validate
     * @return void
     * @throws Exception If data format is invalid
     */
    private function validateData($data): void
    {
        if (!$data || !is_array($data)) {
            throw new Exception("Invalid data format - expected array");
        }

        if (empty($data)) {
            throw new Exception("No data received from remote source");
        }
    }

    /**
     * Import data into database with transaction management
     *
     * @param array $data Validated data to import
     * @return array Import statistics
     * @throws Exception If database operations fail
     */
    private function importData(array $data): array
    {
        DbConfig::getDbConnection();

        $imported = 0;
        $updated = 0;

        \dibi::begin();

        try {
            foreach ($data as $item) {
                if (!$this->isValidRecord($item)) {
                    continue;
                }

                $stats = $this->upsertRecord($item);
                $imported += $stats['imported'];
                $updated += $stats['updated'];
            }

            \dibi::commit();

        } catch (Exception $e) {
            \dibi::rollback();
            throw new Exception("Database import failed: " . $e->getMessage());
        }

        return ['imported' => $imported, 'updated' => $updated];
    }

    /**
     * Validate individual record structure
     *
     * @param mixed $item Record to validate
     * @return bool True if record is valid
     */
    private function isValidRecord($item): bool
    {
        return is_array($item) &&
               isset($item['jmeno']) &&
               isset($item['prijmeni']) &&
               is_string($item['jmeno']) &&
               is_string($item['prijmeni']) &&
               !empty(trim($item['jmeno'])) &&
               !empty(trim($item['prijmeni']));
    }

    /**
     * Insert or update record in database
     *
     * @param array $item Record data
     * @return array Operation statistics
     */
    private function upsertRecord(array $item): array
    {
        $exists = \dibi::query(
            'SELECT id FROM `zaznamy` WHERE jmeno = %s AND prijmeni = %s',
            trim($item['jmeno']),
            trim($item['prijmeni'])
        )->fetch();

        if ($exists) {
            \dibi::query('UPDATE `zaznamy` SET', [
                'datum' => $item['date'] ?? null
            ], 'WHERE id = %i', $exists['id']);

            return ['imported' => 0, 'updated' => 1];
        } else {
            \dibi::query('INSERT INTO `zaznamy`', [
                'jmeno' => trim($item['jmeno']),
                'prijmeni' => trim($item['prijmeni']),
                'datum' => $item['date'] ?? null
            ]);

            return ['imported' => 1, 'updated' => 0];
        }
    }

    /**
     * Redirect to success page
     *
     * @return void
     */
    private function redirectWithSuccess(): void
    {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
        header("Location: " . $protocol . $_SERVER['HTTP_HOST'] . "/?success=import");
        exit;
    }
}


