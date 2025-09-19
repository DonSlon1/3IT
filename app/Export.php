<?php namespace app;


class Export implements App
{
    public function run(): void
    {
        try {
            $format = $_GET['format'] ?? 'csv';

            if (!in_array($format, ['csv', 'json'])) {
                throw new \Exception('Nepodporovaný formát exportu');
            }

            DbConfig::getDbConnection();

            // Get all records
            $records = \dibi::query('SELECT * FROM `zaznamy` ORDER BY datum DESC')->fetchAll();

            $filename = 'data_export_' . date('Y-m-d_H-i-s') . '.' . $format;

            switch ($format) {
                case 'csv':
                    $this->exportCsv($records, $filename);
                    break;
                case 'json':
                    $this->exportJson($records, $filename);
                    break;
            }

        } catch (\Exception $e) {
            ErrorHandler::handleException($e, 'Chyba při exportu dat: ' . $e->getMessage());
        }
    }

    private function exportCsv(array $records, string $filename): void
    {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Expires: 0');

        $output = fopen('php://output', 'w');

        // UTF-8 BOM for proper encoding in Excel
        fwrite($output, "\xEF\xBB\xBF");

        // CSV headers
        fputcsv($output, ['ID', 'Jméno', 'Příjmení', 'Datum'], ';');

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

    private function exportJson(array $records, string $filename): void
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Expires: 0');

        $exportData = [
            'metadata' => [
                'exported_at' => date('c'),
                'total_records' => count($records),
                'format' => 'json',
                'version' => '1.0'
            ],
            'data' => array_map(function($record) {
                return [
                    'id' => (int)$record['id'],
                    'jmeno' => $record['jmeno'],
                    'prijmeni' => $record['prijmeni'],
                    'datum' => $record['datum'],
                    'full_name' => $record['jmeno'] . ' ' . $record['prijmeni']
                ];
            }, $records)
        ];

        echo json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }
}