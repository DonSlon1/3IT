<?php

declare(strict_types=1);

namespace app;

use Exception;

/**
 * Data export controller for CSV and JSON formats
 *
 * Handles secure data export with proper formatting, encoding,
 * and browser-friendly download headers for various file formats.
 */
class Export implements App
{
    private const SUPPORTED_FORMATS = ['csv', 'json'];
    private const DEFAULT_FORMAT = 'csv';

    /**
     * Execute data export process
     *
     * Validates format, retrieves data, and initiates download
     * with proper headers and formatting.
     *
     * @return void
     */
    public function run(): void
    {
        try {
            $format = $this->getValidatedFormat();
            $records = $this->fetchRecords();
            $filename = $this->generateFilename($format);

            $this->exportData($records, $filename, $format);

        } catch (Exception $e) {
            ErrorHandler::handleException($e, 'Export failed: ' . $e->getMessage());
        }
    }

    /**
     * Get and validate export format from request
     *
     * @return string Validated export format
     * @throws Exception If format is not supported
     */
    private function getValidatedFormat(): string
    {
        $format = $_GET['format'] ?? self::DEFAULT_FORMAT;

        if (!in_array($format, self::SUPPORTED_FORMATS, true)) {
            throw new Exception('Unsupported export format: ' . $format);
        }

        return $format;
    }

    /**
     * Fetch all records from database
     *
     * @return array Database records ordered by date
     * @throws Exception If database query fails
     */
    private function fetchRecords(): array
    {
        DbConfig::getDbConnection();

        $records = \dibi::query('SELECT * FROM `zaznamy` ORDER BY datum DESC')->fetchAll();

        if (empty($records)) {
            throw new Exception('No data available for export');
        }

        return $records;
    }

    /**
     * Generate filename with timestamp
     *
     * @param string $format File format extension
     * @return string Generated filename
     */
    private function generateFilename(string $format): string
    {
        return 'data_export_' . date('Y-m-d_H-i-s') . '.' . $format;
    }

    /**
     * Export data in specified format
     *
     * @param array $records Data to export
     * @param string $filename Output filename
     * @param string $format Export format
     * @return void
     */
    private function exportData(array $records, string $filename, string $format): void
    {
        switch ($format) {
            case 'csv':
                $this->exportCsv($records, $filename);
                break;
            case 'json':
                $this->exportJson($records, $filename);
                break;
            default:
                throw new Exception('Unknown export format: ' . $format);
        }
    }

    /**
     * Export data as CSV file
     *
     * @param array $records Database records to export
     * @param string $filename Output filename
     * @return void
     */
    private function exportCsv(array $records, string $filename): void
    {
        $this->setDownloadHeaders('text/csv', $filename);

        $output = fopen('php://output', 'w');

        if ($output === false) {
            throw new Exception('Failed to open output stream');
        }

        // UTF-8 BOM for proper encoding in Excel
        fwrite($output, "\xEF\xBB\xBF");

        // CSV headers
        fputcsv($output, ['ID', 'First Name', 'Last Name', 'Date'], ';');

        // CSV data
        foreach ($records as $record) {
            fputcsv($output, [
                $record['id'],
                $record['jmeno'],
                $record['prijmeni'],
                $record['datum'] ? date('d.m.Y', strtotime($record['datum'])) : ''
            ], ';');
        }

        fclose($output);
        exit;
    }

    /**
     * Export data as JSON file
     *
     * @param array $records Database records to export
     * @param string $filename Output filename
     * @return void
     */
    private function exportJson(array $records, string $filename): void
    {
        $this->setDownloadHeaders('application/json', $filename);

        $exportData = [
            'metadata' => [
                'exported_at' => date('c'),
                'total_records' => count($records),
                'format' => 'json',
                'version' => '1.0'
            ],
            'data' => array_map([$this, 'formatRecordForJson'], $records)
        ];

        echo json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        exit;
    }

    /**
     * Set HTTP headers for file download
     *
     * @param string $contentType MIME type
     * @param string $filename Download filename
     * @return void
     */
    private function setDownloadHeaders(string $contentType, string $filename): void
    {
        header('Content-Type: ' . $contentType . '; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Expires: 0');
        header('Pragma: public');
    }

    /**
     * Format database record for JSON export
     *
     * @param array $record Database record
     * @return array Formatted record
     */
    private function formatRecordForJson(array $record): array
    {
        return [
            'id' => (int)$record['id'],
            'first_name' => $record['jmeno'],
            'last_name' => $record['prijmeni'],
            'date' => $record['datum'],
            'full_name' => trim($record['jmeno'] . ' ' . $record['prijmeni'])
        ];
    }
}