<?php

declare(strict_types=1);

namespace app;

use Exception;

/**
 * Records table controller with sorting and marking functionality
 *
 * Displays paginated data table with sortable columns, record marking,
 * and session-based state persistence for user selections.
 */
class Tabulka implements App
{
    private const ALLOWED_COLUMNS = ['id', 'jmeno', 'prijmeni', 'datum'];
    private const DEFAULT_ORDER_BY = 'datum';
    private const DEFAULT_DIRECTION = 'DESC';

    /**
     * Render the records table page
     *
     * Fetches records with marking status, handles sorting parameters,
     * and renders the table view with current user selections.
     *
     * @return void
     */
    public function run(): void
    {
        try {
            $this->ensureSessionStarted();
            $this->establishDatabaseConnection();

            $sortParams = $this->getSortingParameters();
            $records = $this->fetchRecordsWithMarkingStatus($sortParams);
            $statistics = $this->calculateStatistics();
            $successMessage = $this->getSuccessMessage();

            $this->renderTableView($records, $sortParams, $statistics, $successMessage);

        } catch (Exception $e) {
            ErrorHandler::handleException($e, 'Failed to load records table');
        }
    }

    /**
     * Ensure session is started for marking persistence
     *
     * @return void
     */
    private function ensureSessionStarted(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Establish database connection
     *
     * @return void
     */
    private function establishDatabaseConnection(): void
    {
        DbConfig::getDbConnection();
    }

    /**
     * Get and validate sorting parameters from request
     *
     * @return array Validated sorting parameters
     */
    private function getSortingParameters(): array
    {
        $orderBy = $_GET['order'] ?? self::DEFAULT_ORDER_BY;
        $direction = strtoupper($_GET['dir'] ?? self::DEFAULT_DIRECTION);

        // Validate column name
        if (!in_array($orderBy, self::ALLOWED_COLUMNS, true)) {
            $orderBy = self::DEFAULT_ORDER_BY;
        }

        // Validate direction
        if (!in_array($direction, ['ASC', 'DESC'], true)) {
            $direction = self::DEFAULT_DIRECTION;
        }

        return [
            'orderBy' => $orderBy,
            'direction' => $direction
        ];
    }

    /**
     * Fetch records with marking status information
     *
     * @param array $sortParams Sorting parameters
     * @return array Records with marking information
     */
    private function fetchRecordsWithMarkingStatus(array $sortParams): array
    {
        $sessionId = session_id();

        return \dibi::query('
            SELECT z.*,
                   IF(m.id IS NOT NULL, 1, 0) as is_marked
            FROM `zaznamy` z
            LEFT JOIN `marked_records` m
                ON z.id = m.zaznam_id
                AND m.session_id = %s
            ORDER BY %n %sql',
            $sessionId,
            $sortParams['orderBy'],
            $sortParams['direction']
        )->fetchAll();
    }

    /**
     * Calculate table statistics
     *
     * @return array Statistics including total and marked counts
     */
    private function calculateStatistics(): array
    {
        $sessionId = session_id();

        $totalCount = \dibi::query('SELECT COUNT(*) FROM `zaznamy`')->fetchSingle();

        $markedCount = \dibi::query('
            SELECT COUNT(DISTINCT zaznam_id)
            FROM `marked_records`
            WHERE session_id = %s',
            $sessionId
        )->fetchSingle();

        return [
            'totalCount' => (int)$totalCount,
            'markedCount' => (int)$markedCount
        ];
    }

    /**
     * Get success message from query parameters
     *
     * @return string|null Success message or null
     */
    private function getSuccessMessage(): ?string
    {
        if (!isset($_GET['success'])) {
            return null;
        }

        return match($_GET['success']) {
            'import' => 'Data has been successfully imported.',
            default => null
        };
    }

    /**
     * Render the table view with all data
     *
     * @param array $records Database records
     * @param array $sortParams Sorting parameters
     * @param array $statistics Table statistics
     * @param string|null $successMessage Success message
     * @return void
     */
    private function renderTableView(
        array $records,
        array $sortParams,
        array $statistics,
        ?string $successMessage
    ): void {
        $engine = Latte::getEngine();
        $engine->render(__DIR__ . '/tabulka.latte', [
            'res' => $records,
            'orderBy' => $sortParams['orderBy'],
            'direction' => $sortParams['direction'],
            'successMessage' => $successMessage,
            'totalCount' => $statistics['totalCount'],
            'markedCount' => $statistics['markedCount']
        ]);
    }
}
